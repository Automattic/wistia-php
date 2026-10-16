<?php
declare(strict_types=1);

namespace Automattic\Wistia\Media;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class ListDeletedMediaDataItem
{
    public function __construct(
        #[\SensitiveParameter] public ?string $hashed_id,
        #[\SensitiveParameter] public ?string $name,
        #[\SensitiveParameter] public ?string $type,
        #[\SensitiveParameter] public ?float $duration,
        #[\SensitiveParameter] public ?string $deleted_at,
        #[\SensitiveParameter] public ?string $restore_expires_at,
        #[\SensitiveParameter] public ?ListDeletedMediaDataItemFolder $folder,
        #[\SensitiveParameter] public ?string $cursor,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['hashed_id', 'name', 'type', 'duration', 'deleted_at', 'restore_expires_at', 'folder', 'cursor'], []);
        return new self(
            property_exists($value, 'hashed_id') ? Wire::string($value->hashed_id) : null,
            property_exists($value, 'name') ? Wire::string($value->name) : null,
            property_exists($value, 'type') ? Wire::string($value->type) : null,
            property_exists($value, 'duration') ? ($value->duration === null ? null : Wire::number($value->duration)) : null,
            property_exists($value, 'deleted_at') ? Wire::string($value->deleted_at) : null,
            property_exists($value, 'restore_expires_at') ? Wire::string($value->restore_expires_at) : null,
            property_exists($value, 'folder') ? ($value->folder === null ? null : ListDeletedMediaDataItemFolder::fromWire($value->folder)) : null,
            property_exists($value, 'cursor') ? ($value->cursor === null ? null : Wire::string($value->cursor)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
