<?php
declare(strict_types=1);

namespace Automattic\Wistia\Accessibility;

use Automattic\Wistia\Media\Wire;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class ShowMediaExtendedAudioDescriptionData
{
    public function __construct(
        #[\SensitiveParameter] public ?ShowMediaExtendedAudioDescriptionDataAssets $assets,
        #[\SensitiveParameter] public ?string $id,
        #[\SensitiveParameter] public ?string $ietf_language_tag,
        #[\SensitiveParameter] public ?string $created_at,
        #[\SensitiveParameter] public ?string $updated_at,
        #[\SensitiveParameter] public ?ListMediaExtendedAudioDescriptionsDataItemMedia $media,
        #[\SensitiveParameter] public ?ListMediaExtendedAudioDescriptionsDataItemMedia $provided_media_file,
        #[\SensitiveParameter] public ?ListMediaExtendedAudioDescriptionsDataItemMedia $contact,
        #[\SensitiveParameter] public ?string $cursor,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['assets', 'id', 'ietf_language_tag', 'created_at', 'updated_at', 'media', 'provided_media_file', 'contact', 'cursor'], []);
        return new self(
            property_exists($value, 'assets') ? ShowMediaExtendedAudioDescriptionDataAssets::fromWire($value->assets) : null,
            property_exists($value, 'id') ? Wire::string($value->id) : null,
            property_exists($value, 'ietf_language_tag') ? Wire::string($value->ietf_language_tag) : null,
            property_exists($value, 'created_at') ? Wire::string($value->created_at) : null,
            property_exists($value, 'updated_at') ? Wire::string($value->updated_at) : null,
            property_exists($value, 'media') ? ListMediaExtendedAudioDescriptionsDataItemMedia::fromWire($value->media) : null,
            property_exists($value, 'provided_media_file') ? ListMediaExtendedAudioDescriptionsDataItemMedia::fromWire($value->provided_media_file) : null,
            property_exists($value, 'contact') ? ListMediaExtendedAudioDescriptionsDataItemMedia::fromWire($value->contact) : null,
            property_exists($value, 'cursor') ? ($value->cursor === null ? null : Wire::string($value->cursor)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
