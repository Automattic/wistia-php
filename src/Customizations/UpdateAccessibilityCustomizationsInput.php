<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateAccessibilityCustomizationsInput implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public string|null|Omitted $captionsBackgroundColor = Omitted::Value,
        #[\SensitiveParameter] public int|null|Omitted $captionsBorderRadius = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $captionsTextColor = Omitted::Value,
        #[\SensitiveParameter] public int|null|Omitted $captionsTextSize = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $captionsFontFamily = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $transcriptEnabled = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $showTranscriptSpeakers = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $audioDescriptionControl = Omitted::Value,
        #[\SensitiveParameter] public UpdateAccessibilityCustomizationsInputPlugin|null|Omitted $plugin = Omitted::Value,
    ) {
        if ($this->captionsBackgroundColor !== Omitted::Value && $this->captionsBackgroundColor !== null) { Shape::check(Shape::encode($this->captionsBackgroundColor), ['type' => 'string'], true); }
        if ($this->captionsBorderRadius !== Omitted::Value && $this->captionsBorderRadius !== null) { Shape::check(Shape::encode($this->captionsBorderRadius), ['type' => 'integer'], true); }
        if ($this->captionsTextColor !== Omitted::Value && $this->captionsTextColor !== null) { Shape::check(Shape::encode($this->captionsTextColor), ['type' => 'string'], true); }
        if ($this->captionsTextSize !== Omitted::Value && $this->captionsTextSize !== null) { Shape::check(Shape::encode($this->captionsTextSize), ['type' => 'integer'], true); }
        if ($this->captionsFontFamily !== Omitted::Value && $this->captionsFontFamily !== null) { Shape::check(Shape::encode($this->captionsFontFamily), ['type' => 'string'], true); }
        if ($this->transcriptEnabled !== Omitted::Value && $this->transcriptEnabled !== null) { Shape::check(Shape::encode($this->transcriptEnabled), ['type' => 'boolean'], true); }
        if ($this->showTranscriptSpeakers !== Omitted::Value && $this->showTranscriptSpeakers !== null) { Shape::check(Shape::encode($this->showTranscriptSpeakers), ['type' => 'boolean'], true); }
        if ($this->audioDescriptionControl !== Omitted::Value && $this->audioDescriptionControl !== null) { Shape::check(Shape::encode($this->audioDescriptionControl), ['type' => 'boolean'], true); }
        if ($this->plugin !== Omitted::Value && $this->plugin !== null) { Shape::check(Shape::encode($this->plugin), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['captions' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'onByDefault' => ['type' => 'boolean']]], 'captions-v1' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'onByDefault' => ['type' => 'boolean']]], 'extendedAudioDescription' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean']]]]], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->captionsBackgroundColor !== Omitted::Value) { $out->{'captionsBackgroundColor'} = Shape::encode($this->captionsBackgroundColor); }
        if ($this->captionsBorderRadius !== Omitted::Value) { $out->{'captionsBorderRadius'} = Shape::encode($this->captionsBorderRadius); }
        if ($this->captionsTextColor !== Omitted::Value) { $out->{'captionsTextColor'} = Shape::encode($this->captionsTextColor); }
        if ($this->captionsTextSize !== Omitted::Value) { $out->{'captionsTextSize'} = Shape::encode($this->captionsTextSize); }
        if ($this->captionsFontFamily !== Omitted::Value) { $out->{'captionsFontFamily'} = Shape::encode($this->captionsFontFamily); }
        if ($this->transcriptEnabled !== Omitted::Value) { $out->{'transcriptEnabled'} = Shape::encode($this->transcriptEnabled); }
        if ($this->showTranscriptSpeakers !== Omitted::Value) { $out->{'showTranscriptSpeakers'} = Shape::encode($this->showTranscriptSpeakers); }
        if ($this->audioDescriptionControl !== Omitted::Value) { $out->{'audioDescriptionControl'} = Shape::encode($this->audioDescriptionControl); }
        if ($this->plugin !== Omitted::Value) { $out->{'plugin'} = Shape::encode($this->plugin); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
