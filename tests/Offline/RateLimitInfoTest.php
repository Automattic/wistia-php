<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\Http\{RateLimitInfo, TransportResponse};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RateLimitInfoTest extends TestCase
{
    #[DataProvider('unsafeMetadata')]
    public function testUntrustedHeaderTextIsDiscardedWithoutAnUnsafeCause(string $value): void
    {
        $info = RateLimitInfo::fromResponse(new TransportResponse(429, ['Retry-After' => [$value], 'X-RateLimit-Limit' => [$value], 'X-RateLimit-Remaining' => [$value], 'X-RateLimit-Reset' => [$value]], 'fixture-secret'));
        self::assertNull($info->retryAfter);
        self::assertNull($info->limit);
        self::assertNull($info->remaining);
        self::assertNull($info->reset);
    }

    #[DataProvider('numericHeaders')]
    public function testNumericHeadersRespectLengthAndNativeIntegerCapacity(string $value, ?int $expected): void
    {
        $info = RateLimitInfo::fromResponse(new TransportResponse(429, [
            'X-RateLimit-Limit' => [$value], 'X-RateLimit-Remaining' => [$value], 'X-RateLimit-Reset' => [$value],
        ], ''));
        self::assertSame($expected, $info->limit);
        self::assertSame($expected, $info->remaining);
        self::assertSame($expected, $info->reset);
    }

    public static function numericHeaders(): array
    {
        return [
            ['0', 0], ['0000000000', 0], ['0000000042', 42], ['2147483647', 2147483647],
            ['2147483648', PHP_INT_SIZE >= 8 ? 2147483648 : null],
            ['3000000000', PHP_INT_SIZE >= 8 ? 3000000000 : null],
            ['9999999999', PHP_INT_SIZE >= 8 ? 9999999999 : null],
            [(string) PHP_INT_MAX, PHP_INT_SIZE >= 8 ? null : PHP_INT_MAX],
            ['00000000000', null], ['10000000000', null], ['+1', null], ['1.0', null],
        ];
    }

    public function testRetryAfterRemainsAValidatedStringNotANativeInteger(): void
    {
        foreach (['0', '0000000042', '9999999999', 'Wed, 21 Oct 2015 07:28:00 GMT'] as $value) {
            $info = RateLimitInfo::fromResponse(new TransportResponse(429, ['Retry-After' => [$value]], ''));
            self::assertSame($value, $info->retryAfter);
        }
    }

    public static function unsafeMetadata(): array
    {
        return [['fixture-secret'], ["Wed, 21 Oct 2015 07:28:00 GMT\0fixture-secret"], ['-1'], ['999999999999999999999999999']];
    }
}
