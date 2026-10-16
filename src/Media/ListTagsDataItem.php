<?php
declare(strict_types=1);

namespace Automattic\Wistia\Media;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class ListTagsDataItem
{
    public function __construct(
        #[\SensitiveParameter] public ?string $name,
        #[\SensitiveParameter] public ?int $taggings_count,
        #[\SensitiveParameter] public ?string $created_at,
        #[\SensitiveParameter] public ?string $updated_at,
        #[\SensitiveParameter] public ?string $cursor,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['name', 'taggings_count', 'created_at', 'updated_at', 'cursor'], []);
        return new self(
            property_exists($value, 'name') ? Wire::string($value->name) : null,
            property_exists($value, 'taggings_count') ? Wire::integer($value->taggings_count) : null,
            property_exists($value, 'created_at') ? Wire::string($value->created_at) : null,
            property_exists($value, 'updated_at') ? Wire::string($value->updated_at) : null,
            property_exists($value, 'cursor') ? ($value->cursor === null ? null : Wire::string($value->cursor)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
