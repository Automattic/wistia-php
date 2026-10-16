<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\Configuration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{
    public function testTokenAndFiniteDefaults(): void
    {
        $config = new Configuration('fixture-token');
        self::assertSame('Bearer fixture-token', $config->authorization());
        self::assertSame(30.0, $config->timeout);
        self::assertSame(10.0, $config->connectTimeout);
        self::assertSame('2026-09', Configuration::API_VERSION);
        self::assertStringNotContainsString('fixture-token', print_r($config, true));
    }

    #[DataProvider('invalidConfigurations')]
    public function testRejectsInvalidConfiguration(?string $token, float $timeout, float $connect): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Configuration($token, $timeout, $connect);
    }

    public static function invalidConfigurations(): array
    {
        return [['', 30, 10], ["token\r\nInjected: header", 30, 10], ['two words', 30, 10],
            ['token', 0, 10], ['token', INF, 10], ['token', 30, NAN], ['token', 30, -1]];
    }

    #[DataProvider('unsupportedTimeouts')]
    public function testRejectsTimeoutsOutsidePortableCurlRange(string $field, float $seconds): void
    {
        try {
            new Configuration(...['token' => 'private-timeout-token', $field => $seconds]);
            self::fail('Unsupported timeout was accepted.');
        } catch (\InvalidArgumentException $error) {
            self::assertSame('Timeouts must be finite seconds from 0.001 through 2147483.647 inclusive.', $error->getMessage());
            self::assertStringNotContainsString('private-timeout-token', (string) $error);
        }
    }

    public static function unsupportedTimeouts(): iterable
    {
        foreach (['timeout', 'connectTimeout'] as $field) {
            foreach ([0.0, -1.0, 0.0005, 0.000999999, 2147483.648, PHP_FLOAT_MAX, INF, -INF, NAN] as $index => $value) {
                yield $field . '-' . $index => [$field, $value];
            }
        }
    }

    public function testPortableCurlTimeoutBoundariesHaveSafeIntegerMilliseconds(): void
    {
        foreach ([[0.001, 1], [2147483.647, 2147483647]] as [$seconds, $milliseconds]) {
            $config = new Configuration(timeout: $seconds, connectTimeout: $seconds);
            self::assertSame($seconds, $config->timeout);
            self::assertSame($seconds, $config->connectTimeout);
            self::assertSame($milliseconds, (int) ($config->timeout * 1000));
            self::assertSame($milliseconds, (int) ($config->connectTimeout * 1000));
        }
    }

    public function testTokenlessConfigurationDoesNotPretendToHaveBearerCredentials(): void
    {
        $config = new Configuration();
        $this->expectException(\InvalidArgumentException::class);
        $config->authorization();
    }

    public function testCredentialsCannotBeSerialized(): void
    {
        $this->expectException(\LogicException::class);
        serialize(new Configuration('fixture-token'));
    }
}
