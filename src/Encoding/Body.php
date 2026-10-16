<?php
declare(strict_types=1);

namespace Automattic\Wistia\Encoding;

final readonly class Body
{
    private function __construct(public string $contentType, private string $contents) {}

    /** Exact keys and explicit null are preserved; an omitted key is absent. */
    public static function json(#[\SensitiveParameter] mixed $value): self
    {
        try {
            return new self('application/json', json_encode($value, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION));
        } catch (\Throwable) {
            throw new \InvalidArgumentException('The request body is not JSON encodable.');
        }
    }

    public static function form(#[\SensitiveParameter] Query $fields): self
    {
        return new self('application/x-www-form-urlencoded', $fields->encode());
    }

    public function contents(): string { return $this->contents; }
    public function __debugInfo(): array { return ['contentType' => $this->contentType]; }
}
