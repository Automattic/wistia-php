<?php
declare(strict_types=1);

// Import a source URL from WISTIA_SOURCE_URL only with explicit --execute.
// Follow the 'import-url' branch and import() helper in Application.php.
// Composer loads library classes; __DIR__ keeps this independent of the shell cwd.
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once __DIR__ . '/Application.php';

// Keep a callable entry so offline tests can supply configuration, capture output
// and inject a controlled client/clock without duplicating the workflow.
$example = static fn(array $args, callable $env, callable $out,
    ?\Automattic\Wistia\Client $client = null,
    ?\Automattic\Wistia\Jobs\PollingRuntime $runtime = null): int =>
    \WistiaExamples\Application::run('import-url', $args, $env, $out, $client, $runtime);

// Running this file directly uses real environment/output and returns a process
// exit code. Requiring it from a test returns the callable without running it.
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    exit($example(array_slice($argv, 1), getenv(...), static function (string $line): void {
        echo $line, PHP_EOL;
    }));
}
return $example;
