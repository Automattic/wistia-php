<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateAccessibilityCustomizationsInputPlugin implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public UpdateAccessibilityCustomizationsInputPluginCaptions|null|Omitted $captions = Omitted::Value,
        #[\SensitiveParameter] public UpdateAccessibilityCustomizationsInputPluginCaptionsV1|null|Omitted $captions_v1 = Omitted::Value,
        #[\SensitiveParameter] public UpdateAccessibilityCustomizationsInputPluginExtendedAudioDescription|null|Omitted $extendedAudioDescription = Omitted::Value,
    ) {
        if ($this->captions !== Omitted::Value && $this->captions !== null) { Shape::check(Shape::encode($this->captions), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'onByDefault' => ['type' => 'boolean']]], true); }
        if ($this->captions_v1 !== Omitted::Value && $this->captions_v1 !== null) { Shape::check(Shape::encode($this->captions_v1), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'onByDefault' => ['type' => 'boolean']]], true); }
        if ($this->extendedAudioDescription !== Omitted::Value && $this->extendedAudioDescription !== null) { Shape::check(Shape::encode($this->extendedAudioDescription), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean']]], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->captions !== Omitted::Value) { $out->{'captions'} = Shape::encode($this->captions); }
        if ($this->captions_v1 !== Omitted::Value) { $out->{'captions-v1'} = Shape::encode($this->captions_v1); }
        if ($this->extendedAudioDescription !== Omitted::Value) { $out->{'extendedAudioDescription'} = Shape::encode($this->extendedAudioDescription); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
