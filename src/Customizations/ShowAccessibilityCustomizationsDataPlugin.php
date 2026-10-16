<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowAccessibilityCustomizationsDataPlugin
{
    public function __construct(
        #[\SensitiveParameter] public ShowAccessibilityCustomizationsDataPluginCaptions|null $captions,
        #[\SensitiveParameter] public ShowAccessibilityCustomizationsDataPluginCaptionsV1|null $captions_v1,
        #[\SensitiveParameter] public ShowAccessibilityCustomizationsDataPluginExtendedAudioDescription|null $extendedAudioDescription,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['captions' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string'], 'onByDefault' => ['type' => 'string']]], 'captions-v1' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string'], 'onByDefault' => ['type' => 'string']]], 'extendedAudioDescription' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string']]]]]);
        return new self(
            property_exists($value, 'captions') ? ShowAccessibilityCustomizationsDataPluginCaptions::fromWire($value->{'captions'}) : null,
            property_exists($value, 'captions-v1') ? ShowAccessibilityCustomizationsDataPluginCaptionsV1::fromWire($value->{'captions-v1'}) : null,
            property_exists($value, 'extendedAudioDescription') ? ShowAccessibilityCustomizationsDataPluginExtendedAudioDescription::fromWire($value->{'extendedAudioDescription'}) : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['captions', 'captions-v1', 'extendedAudioDescription'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
