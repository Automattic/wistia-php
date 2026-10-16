<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\Exception\WistiaException;
use Automattic\Wistia\Http\RateLimitInfo;
use PHPUnit\Framework\TestCase;

final class ExceptionAssertions
{
    public static function assertSanitized(WistiaException $exception, string $secret = 'fixture-secret'): void
    {
        TestCase::assertNull($exception->getPrevious());
        TestCase::assertStringNotContainsString($secret, (string) $exception);
        TestCase::assertStringNotContainsString($secret, json_encode([
            $exception->getMessage(), $exception->getCode(), get_object_vars($exception),
        ], JSON_THROW_ON_ERROR));

        $libraryFrames = 0;
        foreach ($exception->getTrace() as $frame) {
            $class = $frame['class'] ?? '';
            if (!str_starts_with($class, 'Automattic\\Wistia\\') || str_starts_with($class, __NAMESPACE__ . '\\')) {
                // PHPUnit/application arguments are caller-owned, not sanitized error context.
                continue;
            }
            ++$libraryFrames;
            TestCase::assertLessThanOrEqual(8, $libraryFrames);
            TestCase::assertArrayHasKey('args', $frame, 'Run redaction checks with exception arguments enabled.');
            TestCase::assertLessThanOrEqual(4, count($frame['args']));
            foreach ($frame['args'] as $argument) {
                if ($argument instanceof \SensitiveParameterValue) {
                    continue;
                }
                // Do not recursively render arbitrary objects: fail on unprotected library inputs.
                TestCase::assertTrue($argument === null || is_scalar($argument) || $argument instanceof RateLimitInfo,
                    'Unexpected unprotected argument in ' . $class . '::' . $frame['function']);
                TestCase::assertStringNotContainsString($secret, json_encode($argument, JSON_THROW_ON_ERROR));
            }
        }
        TestCase::assertGreaterThan(0, $libraryFrames);
    }
}
