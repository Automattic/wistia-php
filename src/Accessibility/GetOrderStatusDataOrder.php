<?php
declare(strict_types=1);

namespace Automattic\Wistia\Accessibility;

use Automattic\Wistia\Media\Wire;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class GetOrderStatusDataOrder
{
    public function __construct(
        #[\SensitiveParameter] public string $id,
        #[\SensitiveParameter] public string $order_status,
        #[\SensitiveParameter] public string $created_at,
        #[\SensitiveParameter] public string $updated_at,
        #[\SensitiveParameter] public string $ietf_language_tag,
        #[\SensitiveParameter] public ListMediaExtendedAudioDescriptionsDataItemMedia $media,
        #[\SensitiveParameter] public ?GetOrderStatusDataOrderMediaExtendedAudioDescription $media_extended_audio_description,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['id', 'order_status', 'created_at', 'updated_at', 'ietf_language_tag', 'media', 'media_extended_audio_description'], ['id', 'order_status', 'ietf_language_tag', 'media', 'created_at', 'updated_at']);
        return new self(
            Wire::string($value->id),
            Wire::oneOf(Wire::string($value->order_status), ['in_progress', 'completed', 'cancelled', 'failed']),
            Wire::string($value->created_at),
            Wire::string($value->updated_at),
            Wire::string($value->ietf_language_tag),
            ListMediaExtendedAudioDescriptionsDataItemMedia::fromWire($value->media),
            property_exists($value, 'media_extended_audio_description') ? ($value->media_extended_audio_description === null ? null : GetOrderStatusDataOrderMediaExtendedAudioDescription::fromWire($value->media_extended_audio_description)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
