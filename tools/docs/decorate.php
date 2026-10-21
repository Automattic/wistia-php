<?php

declare(strict_types=1);

// This file is called by build.php after a successful phpDocumentor run.
$site = 'build/site/';
// Graphviz is optional; do not publish an empty graph page when no SVG was generated.
if (!is_file($site . 'graphs/classes.svg') && is_file($site . 'graphs/classes.html')) {
    unlink($site . 'graphs/classes.html');
}
mkdir($site . 'assets');
foreach (['automattic.css', 'wistia-php.svg'] as $asset) {
    copy(__DIR__ . '/assets/' . $asset, $site . 'assets/' . $asset);
}

// Lead with the installation/quick-start guide, keeping the API index addressable.
copy($site . 'index.html', $site . 'api.html');
$home = file_get_contents($site . 'guides/index.html');
file_put_contents($site . 'index.html', str_replace('<base href="../">', '<base href="./">', $home));

require_once __DIR__ . '/navigation.php';

function docsNavigationList(array $items, string $current): string
{
    $html = '<ul class="a8c-nav-list">';
    foreach ($items as $page => $label) {
        if (is_array($label)) {
            $active = array_filter(docsPages($label), static fn (string $key): bool => docsHref($key) === $current);
            $html .= '<li><details class="a8c-nav-group"' . ($active !== [] ? ' open' : '') . '><summary>'
                . htmlspecialchars($page) . '</summary>' . docsNavigationList($label, $current) . '</details></li>';
        } else {
            $href = docsHref($page);
            $active = $href === $current || ($page === '@api' && preg_match('~^(?:classes|namespaces|packages|files|indices)/~', $current));
            $html .= '<li><a href="' . $href . '"' . ($active ? ' aria-current="page"' : '') . '>'
                . htmlspecialchars($label) . '</a></li>';
        }
    }
    return $html . '</ul>';
}

function docsRenderNavigation(string $current): string
{
    $current = $current === 'guides/index.html' ? 'index.html' : $current;
    $html = '<nav aria-label="Documentation">';
    foreach (docsNavigation() as $section => $items) {
        $html .= '<section class="a8c-nav-section"><h2>' . htmlspecialchars($section) . '</h2>'
            . docsNavigationList($items, $current) . '</section>';
    }
    return $html . '</nav>';
}

$masthead = <<<'HTML'
<a class="a8c-skip" href="#docs-content">Skip to content</a>
HTML;
$footer = <<<'HTML'
<footer class="a8c-footer phpdocumentor-section"><span>© 2015–2026 <a href="https://automattic.com/">Automattic</a> · <a href="https://github.com/Automattic/wistia-php/blob/main/LICENSE">GPL-2.0-or-later</a></span><span>Generated with <a href="https://www.phpdoc.org/">phpDocumentor</a> · Hosted at <a href="https://spacefast.com/">Spacefast</a></span></footer>
HTML;

$entries = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($site, FilesystemIterator::SKIP_DOTS));
foreach ($entries as $entry) {
    if ($entry->getExtension() !== 'html') {
        continue;
    }
    $path = $entry->getPathname();
    $relative = substr($path, strlen($site));
    $html = file_get_contents($path);
    $html = str_replace('https://docs.wistia-php.invalid/', '', $html);
    // Guide heading slugs use hyphens where GitHub's Markdown anchors retain underscores.
    $html = preg_replace_callback('~href="(guides/[^"#]+\.html)#([^"]+)"~',
        static fn (array $match): string => 'href="' . $match[1] . '#' . str_replace('_', '-', $match[2]) . '"', $html);
    if ($relative === 'api.html') {
        $html = str_replace('namespaces/default.html#packages', 'api.html#packages', $html);
    }
    if ($relative === 'guides/tutorials/first-api-call.html') {
        // phpDocumentor drops raw Markdown anchors; preserve previously shared installation links here.
        $html = str_replace('<h2>Install the package</h2>',
            '<h2><span id="install-the-development-package" aria-hidden="true"></span>Install the package</h2>', $html);
    }
    // Give guide pages meaningful browser/search titles instead of the identical project title.
    if (preg_match('~<section class="section [^"]*"[^>]*>\s*<h1>(.*?)</h1>~s', $html, $heading)) {
        $heading = trim(strip_tags($heading[1]));
        $title = ($heading === 'Wistia PHP' ? '' : $heading . ' · ') . 'Wistia PHP';
        $html = preg_replace('~<title>.*?</title>~s', '<title>' . $title . '</title>', $html, 1);
    } elseif (preg_match('~<h2 class="phpdocumentor-content__title">(.*?)</h2>~s', $html, $heading)) {
        // Keep the publisher's root namespace visible as API data, not as page branding.
        $label = $relative === 'namespaces/automattic.html' ? 'Namespace reference' : trim(strip_tags($heading[1]));
        $title = $label . ' · Wistia PHP';
        $html = preg_replace('~<title>.*?</title>~s', '<title>' . $title . '</title>', $html, 1);
    }
    // The template's mobile top menu has no items; the separate sidebar menu is functional.
    $html = preg_replace('~<input class="phpdocumentor-header__menu-button"[^>]*>\s*<label.*?</label>~s', '', $html);
    $html = str_replace('type="search"', 'type="search" aria-label="Search PHP API reference"', $html);
    // Remove default phpDocumentor favicons rather than presenting its logo as ours.
    $html = preg_replace('~<link[^>]+(?:rel="icon"|rel="apple-touch-icon")[^>]*>\s*~', '', $html);
    $html = str_replace('</head>', '<link rel="icon" type="image/svg+xml" href="assets/wistia-php.svg"><link rel="stylesheet" href="assets/automattic.css"></head>', $html);
    $html = str_replace('<body id="top">', '<body id="top">' . $masthead, $html);
    $html = str_replace('<main class="phpdocumentor">', '<main class="phpdocumentor" id="docs-content" tabindex="-1">', $html);
    $html = str_replace('<a href="" class="phpdocumentor-title__link">', '<a href="index.html" class="phpdocumentor-title__link">', $html);
    $html = preg_replace('~(<h1 class="phpdocumentor-title">.*?)(</h1>)~s', '$1<span class="a8c-version">2.0.0</span>$2', $html, 1);
    // Replace the default taxonomy sidebar rather than mixing it into the reading navigation.
    $html = preg_replace_callback('~(<aside class="phpdocumentor-column -three phpdocumentor-sidebar">).*?(</aside>)~s',
        static fn (array $match): string => $match[1] . docsRenderNavigation($relative) . $match[2], $html);
    if ($relative === 'index.html' || str_starts_with($relative, 'guides/')) {
        // Let tables retain table layout while a separate wrapper handles narrow screens.
        $html = preg_replace('~(<table[^>]*>.*?</table>)~s', '<div class="a8c-table">$1</div>', $html);
    }
    // Fragment-only URLs must target this page, not the root selected by <base>.
    $html = preg_replace_callback('~href="#([^"]+)"~',
        static fn (array $match): string => 'href="' . $relative . '#'
            . (str_starts_with($relative, 'guides/') ? str_replace('_', '-', $match[1]) : $match[1]) . '"', $html);
    $html = str_replace('</main>', '</main>' . $footer, $html);
    file_put_contents($path, $html);
}

// Old public URLs land on the corresponding rewritten guide. No historical prose is copied.
foreach (require __DIR__ . '/legacy.php' as $old => $target) {
    $html = file_get_contents($site . $target);
    $base = str_repeat('../', substr_count($old, '/'));
    $html = preg_replace('~<base href="[^"]*">~', '<base href="' . $base . '">', $html, 1);
    $html = str_replace('</head>', '<link rel="canonical" href="' . $target . '"><meta http-equiv="refresh" content="0;url=' . $target . '"></head>', $html);
    if (!is_dir(dirname($site . $old))) {
        mkdir(dirname($site . $old), 0755, true);
    }
    file_put_contents($site . $old, $html);
}
