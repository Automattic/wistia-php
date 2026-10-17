<?php

declare(strict_types=1);

require_once __DIR__ . '/navigation.php';

chdir(dirname(__DIR__, 2));
$site = realpath('build/site');
if ($site === false) {
    throw new RuntimeException('Build documentation first.');
}
$errors = [];
$documents = [];
$ids = [];
libxml_use_internal_errors(true);
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($site, FilesystemIterator::SKIP_DOTS)) as $entry) {
    $relative = substr($entry->getPathname(), strlen($site) + 1);
    if ($entry->isLink() || preg_match('~(?:^|/)(?:\.[^/]+|agents|rewrite|vendor|src|tests)(?:/|$)~', $relative)
        || !in_array(strtolower($entry->getExtension()), ['html', 'css', 'js', 'svg', 'png', 'ico'], true)) {
        $errors[] = 'Unexpected published file: ' . $relative;
    }
    if ($entry->getExtension() !== 'html') {
        continue;
    }
    $html = file_get_contents($entry->getPathname());
    foreach (['a8c-masthead', 'assets/automattic-favicon.png', '2.0 development', 'Unreleased', 'a8c-footer', 'Hosted at', 'https://spacefast.com/', 'https://automattic.com/'] as $required) {
        if (!str_contains($html, $required)) {
            $errors[] = $relative . ': missing ' . $required;
        }
    }
    if (str_contains($html, 'docs.wistia-php.invalid')) {
        $errors[] = $relative . ': unresolved staging URL';
    }
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $documents[$relative] = $dom;
    $xpath = new DOMXPath($dom);
    if ($xpath->query('//aside/nav[@aria-label="Documentation"]')->length !== 1
        || $xpath->query('//aside/nav//a[not(parent::li)]')->length !== 0
        || $xpath->query('//aside//section[contains(@class,"phpdocumentor-sidebar__category")]')->length !== 0) {
        $errors[] = $relative . ': inconsistent documentation navigation';
    }
    if ($xpath->query('//aside/nav//a[@aria-current="page"]')->length > 1) {
        $errors[] = $relative . ': multiple current navigation entries';
    }
    $ids[$relative] = [];
    foreach ((new DOMXPath($dom))->query('//*[@id]') as $node) {
        $ids[$relative][$node->getAttribute('id')] = true;
    }
    libxml_clear_errors();
}

function normalizeDocsPath(string $path): string
{
    $segments = [];
    foreach (explode('/', rawurldecode($path)) as $part) {
        if ($part === '..') {
            if ($segments === []) {
                throw new RuntimeException('Link escapes publish root: ' . $path);
            }
            array_pop($segments);
        } elseif ($part !== '' && $part !== '.') {
            $segments[] = $part;
        }
    }
    return implode('/', $segments);
}

foreach ($documents as $relative => $dom) {
    $base = $dom->getElementsByTagName('base')->item(0)?->getAttribute('href') ?? './';
    $base = normalizeDocsPath(dirname($relative) . '/' . $base);
    foreach ((new DOMXPath($dom))->query('//a[@href] | //link[@href] | //script[@src] | //img[@src]') as $node) {
        $href = $node->getAttribute($node->hasAttribute('href') ? 'href' : 'src');
        if ($href === '' || preg_match('~^(?:[a-z][a-z0-9+.-]*:|//)~i', $href)) {
            continue;
        }
        $parts = parse_url($href);
        $path = $parts['path'] ?? '';
        $target = normalizeDocsPath(($path[0] ?? '') === '/' ? $path : $base . '/' . $path);
        if (is_dir($site . '/' . $target)) {
            $target = ($target === '' ? '' : $target . '/') . 'index.html';
        }
        if (!is_file($site . '/' . $target)) {
            $errors[] = $relative . ': broken link ' . $href . ' -> ' . $target;
        } elseif (isset($parts['fragment']) && $parts['fragment'] !== '' && isset($documents[$target])
            && !isset($ids[$target][rawurldecode($parts['fragment'])])) {
            $errors[] = $relative . ': missing anchor ' . $href;
        }
    }
}
foreach (['index.html', 'api.html', 'classes/Automattic-Wistia-Client.html',
    'classes/Automattic-Wistia-DataMediaService.html', ...array_keys(require __DIR__ . '/legacy.php')] as $required) {
    if (!isset($documents[$required])) {
        $errors[] = 'Missing required page: ' . $required;
    }
}
$expectedSources = [];
foreach (docsPages(docsNavigation()) as $key) {
    $expectedSources[] = 'docs/site/' . $key . '.md';
    $page = 'guides/' . $key . '.html';
    if (!isset($documents[$page])) {
        $errors[] = 'Missing guide: ' . $page;
    } elseif ((new DOMXPath($documents[$page]))->query('//aside/nav//a[@aria-current="page"]')->length !== 1) {
        $errors[] = $page . ': missing current navigation entry';
    }
    $markdown = file_get_contents('docs/site/' . $key . '.md');
    if (preg_match('~\\bP[0-9]{2}\\b|Source/evidence:|source SHA-256~', $markdown)) {
        $errors[] = $page . ': maintainer evidence in public guide';
    }
}
$actualSources = [];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator('docs/site', FilesystemIterator::SKIP_DOTS)) as $entry) {
    if ($entry->getExtension() === 'md') {
        $actualSources[] = $entry->getPathname();
    }
}
sort($expectedSources);
sort($actualSources);
if ($expectedSources !== $actualSources) {
    $errors[] = 'Public source pages and navigation allowlist differ.';
}
if ($errors !== []) {
    fwrite(STDERR, implode("\n", array_unique($errors)) . "\n");
    exit(1);
}
echo 'Checked ' . count($documents) . " HTML pages: links, anchors, branding and publish boundaries passed.\n";
