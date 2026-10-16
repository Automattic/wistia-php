<?php
declare(strict_types=1);

namespace Automattic\Wistia\Accessibility;

use Automattic\Wistia\Media\Wire;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class ListCaptionsByMediaDataItemDiarizedSegmentsItemSpeaker
{
    public function __construct(
        #[\SensitiveParameter] public ?string $media_speaker_id,
        #[\SensitiveParameter] public ?string $speaker_profile_id,
        #[\SensitiveParameter] public ?string $detected_speaker_id,
        #[\SensitiveParameter] public string $display_label,
        #[\SensitiveParameter] public ?string $name,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['media_speaker_id', 'speaker_profile_id', 'detected_speaker_id', 'display_label', 'name'], ['media_speaker_id', 'speaker_profile_id', 'detected_speaker_id', 'display_label', 'name']);
        return new self(
            ($value->media_speaker_id === null ? null : Wire::string($value->media_speaker_id)),
            ($value->speaker_profile_id === null ? null : Wire::string($value->speaker_profile_id)),
            ($value->detected_speaker_id === null ? null : Wire::string($value->detected_speaker_id)),
            Wire::string($value->display_label),
            ($value->name === null ? null : Wire::string($value->name)),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
