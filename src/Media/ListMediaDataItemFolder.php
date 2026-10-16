<?php
declare(strict_types=1);

namespace Automattic\Wistia\Media;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class ListMediaDataItemFolder
{
    public function __construct(
        #[\SensitiveParameter] public ?int $id,
        #[\SensitiveParameter] public ?string $name,
        #[\SensitiveParameter] public ?string $hashed_id,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['id', 'name', 'hashed_id'], []);
        return new self(
            property_exists($value, 'id') ? Wire::integer($value->id) : null,
            property_exists($value, 'name') ? Wire::string($value->name) : null,
            property_exists($value, 'hashed_id') ? Wire::string($value->hashed_id) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
