<?php
declare(strict_types=1);

namespace Automattic\Wistia\Media;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class RestoreMediaDataContainer
{
    public function __construct(
        #[\SensitiveParameter] public ?string $type,
        #[\SensitiveParameter] public ?string $name,
        #[\SensitiveParameter] public ?string $hashedId,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['type', 'name', 'hashedId'], []);
        return new self(
            property_exists($value, 'type') ? Wire::string($value->type) : null,
            property_exists($value, 'name') ? Wire::string($value->name) : null,
            property_exists($value, 'hashedId') ? Wire::string($value->hashedId) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
