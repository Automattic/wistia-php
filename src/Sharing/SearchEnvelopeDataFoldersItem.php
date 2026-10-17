<?php
declare(strict_types=1);

namespace Automattic\Wistia\Sharing;

use Automattic\Wistia\Media\Wire;

final readonly class SearchEnvelopeDataFoldersItem
{
    public function __construct(
        #[\SensitiveParameter] public int $id,
        #[\SensitiveParameter] public string $name,
        #[\SensitiveParameter] public string|null $description,
        #[\SensitiveParameter] public int $media_count,
        #[\SensitiveParameter] public SearchEnvelopeDataFoldersItemMedias $medias,
        #[\SensitiveParameter] public string $created,
        #[\SensitiveParameter] public string $updated,
        #[\SensitiveParameter] public string $hashed_id,
        #[\SensitiveParameter] public bool $public,
        #[\SensitiveParameter] public string|null $public_id,
        #[\SensitiveParameter] public bool|null $anonymous_can_upload,
        #[\SensitiveParameter] public bool|null $anonymous_can_download,
        #[\SensitiveParameter] public string $kind,
        #[\SensitiveParameter] public bool $personal_library,
        #[\SensitiveParameter] public string|null $cursor,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['id', 'name', 'description', 'media_count', 'medias', 'created', 'updated', 'hashed_id', 'public', 'public_id', 'anonymous_can_upload', 'anonymous_can_download', 'kind', 'personal_library', 'cursor'], ['id', 'name', 'media_count', 'created', 'updated', 'hashed_id', 'public', 'public_id', 'kind', 'personal_library', 'medias']);
        return new self(
            Wire::integer($value->id),
            Wire::string($value->name),
            property_exists($value, 'description') ? ($value->description === null ? null : Wire::string($value->description)) : null,
            Wire::integer($value->media_count),
            SearchEnvelopeDataFoldersItemMedias::fromWire($value->medias),
            Wire::string($value->created),
            Wire::string($value->updated),
            Wire::string($value->hashed_id),
            Wire::boolean($value->public),
            ($value->public_id === null ? null : Wire::string($value->public_id)),
            property_exists($value, 'anonymous_can_upload') ? Wire::boolean($value->anonymous_can_upload) : null,
            property_exists($value, 'anonymous_can_download') ? Wire::boolean($value->anonymous_can_download) : null,
            Wire::oneOf(Wire::string($value->kind), ['library', 'shared', 'account']),
            Wire::boolean($value->personal_library),
            property_exists($value, 'cursor') ? ($value->cursor === null ? null : Wire::string($value->cursor)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
