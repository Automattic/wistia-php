<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once __DIR__ . '/Application.php';

$example = static fn(array $args, callable $env, callable $out,
    ?\Automattic\Wistia\Client $client = null,
    ?\Automattic\Wistia\Jobs\PollingRuntime $runtime = null): int =>
    \WistiaExamples\Application::run('list-media', $args, $env, $out, $client, $runtime);

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    exit($example(array_slice($argv, 1), getenv(...), static function (string $line): void {
        echo $line, PHP_EOL;
    }));
}
return $example;
