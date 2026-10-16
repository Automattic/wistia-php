<?php
declare(strict_types=1);

namespace Automattic\Wistia\Http;

final readonly class TransportResponse
{
    /** @param array<string, list<string>> $headers */
    public function __construct(
        public int $status,
        #[\SensitiveParameter] private array $headers,
        #[\SensitiveParameter] public string $body,
    ) {}

    public function header(string $name): ?string
    {
        foreach ($this->headers as $key => $values) {
            if (strcasecmp($key, $name) === 0) { return $values[0] ?? null; }
        }
        return null;
    }

    public function __debugInfo(): array { return ['status' => $this->status]; }
}
