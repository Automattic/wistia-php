<?php
declare(strict_types=1);

namespace Automattic\Wistia\Media;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class ListMediaDataItemSpeakersItem
{
    public function __construct(
        #[\SensitiveParameter] public string $media_speaker_id,
        #[\SensitiveParameter] public string $speaker_profile_id,
        #[\SensitiveParameter] public string $name,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['media_speaker_id', 'speaker_profile_id', 'name'], ['media_speaker_id', 'speaker_profile_id', 'name']);
        return new self(
            Wire::string($value->media_speaker_id),
            Wire::string($value->speaker_profile_id),
            Wire::string($value->name),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
