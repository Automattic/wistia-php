<?php

declare(strict_types=1);

// Isolated documentation tooling: never added to the library's Composer dependencies.
require __DIR__ . '/tool.php';

$directory = dirname(__DIR__, 2) . '/.tools';
$target = $directory . '/phpDocumentor.phar';
if (is_file($target) && hash_file('sha256', $target) === PHPDOC_SHA256) {
    echo 'phpDocumentor ' . PHPDOC_VERSION . " is verified.\n";
    exit(0);
}
if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
    throw new RuntimeException('Cannot create documentation tooling directory.');
}
$temporary = tempnam($directory, 'phpdoc-');
try {
    $url = 'https://github.com/phpDocumentor/phpDocumentor/releases/download/v' . PHPDOC_VERSION . '/phpDocumentor.phar';
    $context = stream_context_create(['http' => ['timeout' => 120, 'user_agent' => 'wistia-php-docs']]);
    if (!copy($url, $temporary, $context) || hash_file('sha256', $temporary) !== PHPDOC_SHA256) {
        throw new RuntimeException('phpDocumentor download failed integrity verification.');
    }
    if (!rename($temporary, $target)) {
        throw new RuntimeException('Cannot install verified phpDocumentor.');
    }
} finally {
    if (is_file($temporary)) {
        unlink($temporary);
    }
}
echo 'Installed verified phpDocumentor ' . PHPDOC_VERSION . ".\n";
