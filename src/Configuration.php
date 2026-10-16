<?php
declare(strict_types=1);

namespace Automattic\Wistia;

final readonly class Configuration
{
    public const API_VERSION = '2026-09';

    public function __construct(
        #[\SensitiveParameter] private ?string $token = null,
        public float $timeout = 30.0,
        public float $connectTimeout = 10.0,
    ) {
        if ($token !== null && ($token === '' || preg_match('/[\x00-\x20\x7f]/', $token))) {
            throw new \InvalidArgumentException('A nonempty Bearer token without whitespace is required.');
        }
        // Milliseconds must fit signed 32-bit cURL options on every supported platform.
        foreach ([$timeout, $connectTimeout] as $seconds) {
            if (!is_finite($seconds) || $seconds < 0.001 || $seconds > 2147483.647) {
                throw new \InvalidArgumentException('Timeouts must be finite seconds from 0.001 through 2147483.647 inclusive.');
            }
        }
    }

    /** @internal Only the executor should attach credentials. */
    public function authorization(): string
    {
        if ($this->token === null) {
            throw new \InvalidArgumentException('Configure a Bearer token before calling Data, Stats, Analytics or Upload.');
        }
        return 'Bearer ' . $this->token;
    }

    public function __debugInfo(): array { return ['apiVersion' => self::API_VERSION]; }
    public function __serialize(): array { throw new \LogicException('Credentials cannot be serialized.'); }
}
