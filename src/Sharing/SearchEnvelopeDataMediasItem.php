<?php
declare(strict_types=1);

namespace Automattic\Wistia\Sharing;

use Automattic\Wistia\Media\Wire;

final readonly class SearchEnvelopeDataMediasItem
{
    public function __construct(
        #[\SensitiveParameter] public int|null $id,
        #[\SensitiveParameter] public string|null $name,
        #[\SensitiveParameter] public string|null $type,
        #[\SensitiveParameter] public bool|null $archived,
        #[\SensitiveParameter] public string|null $created,
        #[\SensitiveParameter] public string|null $updated,
        #[\SensitiveParameter] public float|null $duration,
        #[\SensitiveParameter] public string|null $embedCode,
        #[\SensitiveParameter] public string|null $hashed_id,
        #[\SensitiveParameter] public string|null $description,
        #[\SensitiveParameter] public float|null $progress,
        #[\SensitiveParameter] public string|null $status,
        #[\SensitiveParameter] public string|null $section,
        #[\SensitiveParameter] public SearchEnvelopeDataMediasItemThumbnail|null $thumbnail,
        #[\SensitiveParameter] public bool|null $protected,
        #[\SensitiveParameter] public string|null $folder_hashed_id,
        /** @var list<SearchEnvelopeDataMediasItemTranscriptMatchesItem>|null */
        #[\SensitiveParameter] public array $transcript_matches,
        /** @var list<SearchEnvelopeDataMediasItemCustomMetadataFieldValuesItem>|null */
        #[\SensitiveParameter] public array|null $custom_metadata_field_values,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['id', 'name', 'type', 'archived', 'created', 'updated', 'duration', 'embedCode', 'hashed_id', 'description', 'progress', 'status', 'section', 'thumbnail', 'protected', 'folder_hashed_id', 'transcript_matches', 'custom_metadata_field_values'], ['folder_hashed_id', 'transcript_matches']);
        return new self(
            property_exists($value, 'id') ? Wire::integer($value->id) : null,
            property_exists($value, 'name') ? Wire::string($value->name) : null,
            property_exists($value, 'type') ? Wire::oneOf(Wire::string($value->type), ['Video', 'Audio', 'Image', 'PdfDocument', 'MicrosoftOfficeDocument', 'Swf', 'UnknownType']) : null,
            property_exists($value, 'archived') ? Wire::boolean($value->archived) : null,
            property_exists($value, 'created') ? Wire::string($value->created) : null,
            property_exists($value, 'updated') ? Wire::string($value->updated) : null,
            property_exists($value, 'duration') ? ($value->duration === null ? null : Wire::number($value->duration)) : null,
            property_exists($value, 'embedCode') ? Wire::string($value->embedCode) : null,
            property_exists($value, 'hashed_id') ? Wire::string($value->hashed_id) : null,
            property_exists($value, 'description') ? Wire::string($value->description) : null,
            property_exists($value, 'progress') ? Wire::range(Wire::number($value->progress), 0, 1) : null,
            property_exists($value, 'status') ? Wire::oneOf(Wire::string($value->status), ['queued', 'processing', 'ready', 'failed']) : null,
            property_exists($value, 'section') ? ($value->section === null ? null : Wire::string($value->section)) : null,
            property_exists($value, 'thumbnail') ? SearchEnvelopeDataMediasItemThumbnail::fromWire($value->thumbnail) : null,
            property_exists($value, 'protected') ? ($value->protected === null ? null : Wire::boolean($value->protected)) : null,
            ($value->folder_hashed_id === null ? null : Wire::string($value->folder_hashed_id)),
            Wire::items($value->transcript_matches, static fn($item) => SearchEnvelopeDataMediasItemTranscriptMatchesItem::fromWire($item)),
            property_exists($value, 'custom_metadata_field_values') ? Wire::items($value->custom_metadata_field_values, static fn($item) => SearchEnvelopeDataMediasItemCustomMetadataFieldValuesItem::fromWire($item)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
