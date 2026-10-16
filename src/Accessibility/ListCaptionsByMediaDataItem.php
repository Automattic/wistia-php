<?php
declare(strict_types=1);

namespace Automattic\Wistia\Accessibility;

use Automattic\Wistia\Media\Wire;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class ListCaptionsByMediaDataItem
{
    public function __construct(
        #[\SensitiveParameter] public ?string $english_name,
        #[\SensitiveParameter] public ?string $native_name,
        #[\SensitiveParameter] public string $language,
        #[\SensitiveParameter] public ?string $text,
        #[\SensitiveParameter] public bool $is_draft,
        #[\SensitiveParameter] public string $id,
        #[\SensitiveParameter] public string $media_id,
        #[\SensitiveParameter] public ?int $version,
        /** @var list<ListCaptionsByMediaDataItemSegmentsItem>|null */
        #[\SensitiveParameter] public ?array $segments,
        /** @var list<ListCaptionsByMediaDataItemDiarizedSegmentsItem>|null */
        #[\SensitiveParameter] public ?array $diarized_segments,
        #[\SensitiveParameter] public ?string $diarization_status,
        #[\SensitiveParameter] public ?bool $diarization_derivable,
        #[\SensitiveParameter] public ?int $speaker_data_version,
        #[\SensitiveParameter] public ?string $cursor,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['english_name', 'native_name', 'language', 'text', 'is_draft', 'id', 'media_id', 'version', 'segments', 'diarized_segments', 'diarization_status', 'diarization_derivable', 'speaker_data_version', 'cursor'], ['language', 'is_draft', 'id', 'media_id', 'version', 'segments']);
        return new self(
            property_exists($value, 'english_name') ? Wire::string($value->english_name) : null,
            property_exists($value, 'native_name') ? Wire::string($value->native_name) : null,
            Wire::string($value->language),
            property_exists($value, 'text') ? ($value->text === null ? null : Wire::string($value->text)) : null,
            Wire::boolean($value->is_draft),
            Wire::string($value->id),
            Wire::string($value->media_id),
            ($value->version === null ? null : Wire::integer($value->version)),
            ($value->segments === null ? null : Wire::items($value->segments, static fn($v) => ListCaptionsByMediaDataItemSegmentsItem::fromWire($v))),
            property_exists($value, 'diarized_segments') ? Wire::items($value->diarized_segments, static fn($v) => ListCaptionsByMediaDataItemDiarizedSegmentsItem::fromWire($v)) : null,
            property_exists($value, 'diarization_status') ? Wire::oneOf(Wire::string($value->diarization_status), ['ready', 'processing', 'unavailable', 'disabled']) : null,
            property_exists($value, 'diarization_derivable') ? Wire::boolean($value->diarization_derivable) : null,
            property_exists($value, 'speaker_data_version') ? ($value->speaker_data_version === null ? null : Wire::integer($value->speaker_data_version)) : null,
            property_exists($value, 'cursor') ? ($value->cursor === null ? null : Wire::string($value->cursor)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
