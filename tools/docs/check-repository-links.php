<?php

declare(strict_types=1);

chdir(dirname(__DIR__, 2));
$files = ['README.md', 'CONTRIBUTING.md', 'AGENTS.md', 'CHANGELOG.md'];
foreach (['docs', 'examples', 'tools/docs'] as $directory) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)) as $entry) {
        if ($entry->getExtension() === 'md') {
            $files[] = $entry->getPathname();
        }
    }
}

/** GitHub-style heading IDs, including repeated headings and explicit HTML IDs. */
function docsRepositoryAnchors(string $file): array
{
    $text = preg_replace('~```.*?```~s', '', file_get_contents($file));
    preg_match_all('/^#{1,6}\s+(.+)$/m', $text, $matches);
    $ids = [];
    $counts = [];
    foreach ($matches[1] as $heading) {
        $heading = mb_strtolower(strip_tags($heading));
        $heading = preg_replace('/[^\p{L}\p{N}_\-\s]/u', '', $heading);
        $id = preg_replace('/\s+/u', '-', trim($heading));
        $count = $counts[$id] ?? 0;
        $counts[$id] = $count + 1;
        $ids[$id . ($count === 0 ? '' : '-' . $count)] = true;
    }
    preg_match_all('~\b(?:id|name)=["\']([^"\']+)["\']~', $text, $explicit);
    foreach ($explicit[1] as $id) {
        $ids[$id] = true;
    }
    return $ids;
}

$errors = [];
$anchors = [];
$count = 0;
foreach ($files as $file) {
    $text = preg_replace('~```.*?```~s', '', file_get_contents($file));
    preg_match_all('~\[[^\]]+\]\(([^\s)]+)\)~', $text, $links);
    foreach ($links[1] as $href) {
        if (preg_match('~^(?:[a-z][a-z0-9+.-]*:|//)~i', $href)) {
            continue; // No network access; external links have a separate verification boundary.
        }
        ++$count;
        $parts = parse_url($href);
        $path = rawurldecode($parts['path'] ?? '');
        $target = $path === '' ? $file : dirname($file) . '/' . $path;
        if (!file_exists($target)) {
            $errors[] = $file . ': missing local target ' . $href;
            continue;
        }
        $fragment = rawurldecode($parts['fragment'] ?? '');
        if ($fragment !== '' && str_ends_with($target, '.md')) {
            $key = realpath($target);
            $anchors[$key] ??= docsRepositoryAnchors($target);
            if (!isset($anchors[$key][$fragment])) {
                $errors[] = $file . ': missing local anchor ' . $href;
            }
        }
    }
}
if ($errors !== []) {
    fwrite(STDERR, implode("\n", array_unique($errors)) . "\n");
    exit(1);
}
echo 'Checked ' . $count . ' local Markdown links across ' . count($files) . " files; no network requests.\n";
