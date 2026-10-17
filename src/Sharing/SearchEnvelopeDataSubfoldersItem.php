<?php
declare(strict_types=1);

namespace Automattic\Wistia\Sharing;

use Automattic\Wistia\Media\Wire;

final readonly class SearchEnvelopeDataSubfoldersItem
{
    public function __construct(
        #[\SensitiveParameter] public string|null $hashed_id,
        #[\SensitiveParameter] public string|null $name,
        #[\SensitiveParameter] public string|null $description,
        #[\SensitiveParameter] public int|null $position,
        #[\SensitiveParameter] public string|null $created,
        #[\SensitiveParameter] public string|null $updated,
        #[\SensitiveParameter] public string|null $cursor,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['hashed_id', 'name', 'description', 'position', 'created', 'updated', 'cursor'], ['hashed_id', 'position', 'created', 'updated']);
        return new self(
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
