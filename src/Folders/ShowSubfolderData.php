<?php
declare(strict_types=1);

namespace Automattic\Wistia\Folders;

use Automattic\Wistia\Media\Wire;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class ShowSubfolderData
{
    public function __construct(
        /** @var list<ShowSubfolderDataMediasItem>|null */
        #[\SensitiveParameter] public ?array $medias,
        #[\SensitiveParameter] public ?string $hashed_id,
        #[\SensitiveParameter] public ?string $name,
        #[\SensitiveParameter] public ?string $description,
        #[\SensitiveParameter] public ?int $position,
        #[\SensitiveParameter] public ?string $created,
        #[\SensitiveParameter] public ?string $updated,
        #[\SensitiveParameter] public ?string $cursor,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['medias', 'hashed_id', 'name', 'description', 'position', 'created', 'updated', 'cursor'], ['hashed_id', 'position', 'created', 'updated']);
        return new self(
            property_exists($value, 'medias') ? Wire::items($value->medias, static fn($v) => ShowSubfolderDataMediasItem::fromWire($v)) : null,
            ($value->hashed_id === null ? null : Wire::string($value->hashed_id)),
            property_exists($value, 'name') ? ($value->name === null ? null : Wire::string($value->name)) : null,
            property_exists($value, 'description') ? ($value->description === null ? null : Wire::string($value->description)) : null,
            ($value->position === null ? null : Wire::integer($value->position)),
            ($value->created === null ? null : Wire::string($value->created)),
            ($value->updated === null ? null : Wire::string($value->updated)),
            property_exists($value, 'cursor') ? ($value->cursor === null ? null : Wire::string($value->cursor)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
