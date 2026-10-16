<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowAccessibilityCustomizationsData
{
    public function __construct(
        #[\SensitiveParameter] public string|null $captionsBackgroundColor,
        #[\SensitiveParameter] public string|null $captionsBorderRadius,
        #[\SensitiveParameter] public string|null $captionsTextColor,
        #[\SensitiveParameter] public string|null $captionsTextSize,
        #[\SensitiveParameter] public string|null $captionsFontFamily,
        #[\SensitiveParameter] public string|null $transcriptEnabled,
        #[\SensitiveParameter] public string|null $showTranscriptSpeakers,
        #[\SensitiveParameter] public string|null $audioDescriptionControl,
        #[\SensitiveParameter] public ShowAccessibilityCustomizationsDataPlugin|null $plugin,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['captionsBackgroundColor' => ['type' => 'string'], 'captionsBorderRadius' => ['type' => 'string'], 'captionsTextColor' => ['type' => 'string'], 'captionsTextSize' => ['type' => 'string'], 'captionsFontFamily' => ['type' => 'string'], 'transcriptEnabled' => ['type' => 'string'], 'showTranscriptSpeakers' => ['type' => 'string'], 'audioDescriptionControl' => ['type' => 'string'], 'plugin' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['captions' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string'], 'onByDefault' => ['type' => 'string']]], 'captions-v1' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string'], 'onByDefault' => ['type' => 'string']]], 'extendedAudioDescription' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string']]]]]]]);
        return new self(
            property_exists($value, 'captionsBackgroundColor') ? $value->{'captionsBackgroundColor'} : null,
            property_exists($value, 'captionsBorderRadius') ? $value->{'captionsBorderRadius'} : null,
            property_exists($value, 'captionsTextColor') ? $value->{'captionsTextColor'} : null,
            property_exists($value, 'captionsTextSize') ? $value->{'captionsTextSize'} : null,
            property_exists($value, 'captionsFontFamily') ? $value->{'captionsFontFamily'} : null,
            property_exists($value, 'transcriptEnabled') ? $value->{'transcriptEnabled'} : null,
            property_exists($value, 'showTranscriptSpeakers') ? $value->{'showTranscriptSpeakers'} : null,
            property_exists($value, 'audioDescriptionControl') ? $value->{'audioDescriptionControl'} : null,
            property_exists($value, 'plugin') ? ShowAccessibilityCustomizationsDataPlugin::fromWire($value->{'plugin'}) : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['captionsBackgroundColor', 'captionsBorderRadius', 'captionsTextColor', 'captionsTextSize', 'captionsFontFamily', 'transcriptEnabled', 'showTranscriptSpeakers', 'audioDescriptionControl', 'plugin'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
