<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateThumbnailCustomizationsInputPlugin implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public UpdateThumbnailCustomizationsInputPluginVideoThumbnail|null|Omitted $videoThumbnail = Omitted::Value,
        #[\SensitiveParameter] public UpdateThumbnailCustomizationsInputPluginThumbnailTextOverlayV2|null|Omitted $thumbnailTextOverlay_v2 = Omitted::Value,
    ) {
        if ($this->videoThumbnail !== Omitted::Value && $this->videoThumbnail !== null) { Shape::check(Shape::encode($this->videoThumbnail), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['clickToPlayButton' => ['type' => 'boolean'], 'clickForSound' => ['type' => 'boolean'], 'hashedId' => ['type' => 'string'], 'trimStart' => ['type' => 'string'], 'trimEnd' => ['type' => 'string'], 'priorityMode' => ['type' => 'string']]], true); }
        if ($this->thumbnailTextOverlay_v2 !== Omitted::Value && $this->thumbnailTextOverlay_v2 !== null) { Shape::check(Shape::encode($this->thumbnailTextOverlay_v2), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'text' => ['type' => 'string']]], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->videoThumbnail !== Omitted::Value) { $out->{'videoThumbnail'} = Shape::encode($this->videoThumbnail); }
        if ($this->thumbnailTextOverlay_v2 !== Omitted::Value) { $out->{'thumbnailTextOverlay-v2'} = Shape::encode($this->thumbnailTextOverlay_v2); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
