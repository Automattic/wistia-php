<?php
declare(strict_types=1);

namespace Automattic\Wistia\Accessibility;

use Automattic\Wistia\Media\Wire;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class ListLocalizationsDataItemSourceMedia
{
    public function __construct(
        #[\SensitiveParameter] public string $hashed_id,
        #[\SensitiveParameter] public string $name,
        #[\SensitiveParameter] public float $duration,
        #[\SensitiveParameter] public string $created_at,
        #[\SensitiveParameter] public string $updated_at,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['hashed_id', 'name', 'duration', 'created_at', 'updated_at'], ['hashed_id', 'name', 'duration', 'created_at', 'updated_at']);
        return new self(
            Wire::string($value->hashed_id),
            Wire::string($value->name),
            Wire::number($value->duration),
            Wire::string($value->created_at),
            Wire::string($value->updated_at),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
