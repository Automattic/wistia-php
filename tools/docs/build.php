<?php

declare(strict_types=1);

require __DIR__ . '/tool.php';
require_once __DIR__ . '/navigation.php';

$root = dirname(__DIR__, 2);
chdir($root);

/** Clear only generated documentation directories; never follow symlinks. */
function clearDocsDirectory(string $directory): void
{
    if (is_link($directory)) {
        throw new RuntimeException('Refusing symlinked output: ' . $directory);
    }
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
        return;
    }
    $entries = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );
    foreach ($entries as $entry) {
        $entry->isDir() && !$entry->isLink() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
    }
}

if (is_link('build')) {
    throw new RuntimeException('Refusing symlinked build directory.');
}
$tool = '.tools/phpDocumentor.phar';
if (!is_file($tool) || hash_file('sha256', $tool) !== PHPDOC_SHA256) {
    throw new RuntimeException('Run php tools/docs/install.php to install the pinned, verified generator.');
}
clearDocsDirectory('build/guides');
clearDocsDirectory('build/site');

// Publish only the reader-facing pages listed in navigation, never maintainer records.
$destinations = [];
foreach (docsPages(docsNavigation()) as $page) {
    $destinations['docs/site/' . $page . '.md'] = $page . '.md';
}
foreach ($destinations as $file => $destination) {
    $markdown = file_get_contents($file);
    // Keep guide links local; source files, licenses and rewrite evidence remain on GitHub.
    $markdown = preg_replace_callback('~(?<!!)\[([^\]]+)\]\(([^\s)]+)\)~', function (array $match) use ($file, $destinations): string {
        $href = $match[2];
        if (preg_match('~^(?:[a-z][a-z0-9+.-]*:|/|#)~i', $href)) {
            return $match[0];
        }
        [$path, $fragment] = array_pad(explode('#', $href, 2), 2, null);
        $segments = [];
        foreach (explode('/', dirname($file) . '/' . $path) as $segment) {
            if ($segment === '..') {
                array_pop($segments);
            } elseif ($segment !== '.' && $segment !== '') {
                $segments[] = $segment;
            }
        }
        $source = implode('/', $segments);
        if (isset($destinations[$source])) {
            // phpDocumentor 3.10 cannot resolve Markdown links with fragments reliably.
            // A reserved URL preserves them through rendering; decorate.php makes them local.
            $url = 'https://docs.wistia-php.invalid/guides/' . substr($destinations[$source], 0, -3) . '.html';
        } elseif (str_starts_with($source, 'src/') && str_ends_with($source, '.php') && is_file($source)) {
            // Link public source symbols to their generated PHP reference, not raw files.
            $url = 'https://docs.wistia-php.invalid/classes/Automattic-Wistia-'
                . str_replace('/', '-', substr($source, 4, -4)) . '.html';
        } else {
            $url = 'https://github.com/Automattic/wistia-php/blob/main/' . $source;
        }
        return '[' . $match[1] . '](' . $url . ($fragment === null ? '' : '#' . $fragment) . ')';
    }, $markdown);
    $target = 'build/guides/' . $destination;
    if (!is_dir(dirname($target))) {
        mkdir(dirname($target), 0755, true);
    }
    file_put_contents($target, $markdown);
}

passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($tool) . ' --config phpdoc.dist.xml --no-progress', $exit);
if ($exit !== 0) {
    exit($exit);
}
require __DIR__ . '/decorate.php';
echo "Documentation generated in build/site.\n";
