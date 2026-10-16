<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use PHPUnit\Framework\TestCase;

final class ExceptionRedactionTest extends TestCase
{
    public function testAdversarialExceptionTracesWithoutFrameworkCallerGraph(): void
    {
        $process = proc_open([PHP_BINARY, '-d', 'zend.exception_ignore_args=0', __DIR__ . '/fixtures/exception-redaction.php'],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        self::assertIsResource($process);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        self::assertSame(0, proc_close($process), $stderr);
        self::assertSame('', $stderr);
        self::assertSame("Sanitized isolated exception traces: 9 cases; arguments enabled.\n", $stdout);
    }
}
