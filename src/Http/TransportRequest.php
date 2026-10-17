<?php
declare(strict_types=1);

namespace Automattic\Wistia\Http;

final readonly class TransportRequest
{
    /** @param array<string, string> $headers */
    public function __construct(
        public string $method,
        #[\SensitiveParameter] public string $url,
        #[\SensitiveParameter] public array $headers,
        #[\SensitiveParameter] public string|\Psr\Http\Message\StreamInterface|null $body,
        public float $timeout,
        public float $connectTimeout,
    ) {}

    public function __debugInfo(): array { return ['method' => $this->method]; }
    public function __serialize(): array { throw new \LogicException('HTTP requests cannot be serialized.'); }
}
