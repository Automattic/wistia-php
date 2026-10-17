<?php
declare(strict_types=1);

// Read an existing job's status with a finite attempt limit; never create a job.
// Follow the 'poll-job' branch in Application.php for termination/result handling.
// Composer loads library classes; __DIR__ keeps this independent of the shell cwd.
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once __DIR__ . '/Application.php';

// Keep a callable entry so offline tests can supply configuration, capture output
// and inject a controlled client/clock without duplicating the workflow.
$example = static fn(array $args, callable $env, callable $out,
    ?\Automattic\Wistia\Client $client = null,
    ?\Automattic\Wistia\Jobs\PollingRuntime $runtime = null): int =>
    \WistiaExamples\Application::run('poll-job', $args, $env, $out, $client, $runtime);

// Running this file directly uses real environment/output and returns a process
// exit code. Requiring it from a test returns the callable without running it.
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    exit($example(array_slice($argv, 1), getenv(...), static function (string $line): void {
        echo $line, PHP_EOL;
    }));
}
return $example;
