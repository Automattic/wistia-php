<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateThumbnailCustomizationsInput implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public string|null|Omitted $stillUrl = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $thumbnailAltText = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $fitStrategy = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $unalteredStillImageAsset = Omitted::Value,
        #[\SensitiveParameter] public UpdateThumbnailCustomizationsInputPlugin|null|Omitted $plugin = Omitted::Value,
    ) {
        if ($this->stillUrl !== Omitted::Value && $this->stillUrl !== null) { Shape::check(Shape::encode($this->stillUrl), ['type' => 'string'], true); }
        if ($this->thumbnailAltText !== Omitted::Value && $this->thumbnailAltText !== null) { Shape::check(Shape::encode($this->thumbnailAltText), ['type' => 'string'], true); }
        if ($this->fitStrategy !== Omitted::Value && $this->fitStrategy !== null) { Shape::check(Shape::encode($this->fitStrategy), ['type' => 'string', 'enum' => ['contain', 'cover', 'fill', 'none']], true); }
        if ($this->unalteredStillImageAsset !== Omitted::Value && $this->unalteredStillImageAsset !== null) { Shape::check(Shape::encode($this->unalteredStillImageAsset), ['type' => 'string'], true); }
        if ($this->plugin !== Omitted::Value && $this->plugin !== null) { Shape::check(Shape::encode($this->plugin), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['videoThumbnail' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['clickToPlayButton' => ['type' => 'boolean'], 'clickForSound' => ['type' => 'boolean'], 'hashedId' => ['type' => 'string'], 'trimStart' => ['type' => 'string'], 'trimEnd' => ['type' => 'string'], 'priorityMode' => ['type' => 'string']]], 'thumbnailTextOverlay-v2' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'text' => ['type' => 'string']]]]], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->stillUrl !== Omitted::Value) { $out->{'stillUrl'} = Shape::encode($this->stillUrl); }
        if ($this->thumbnailAltText !== Omitted::Value) { $out->{'thumbnailAltText'} = Shape::encode($this->thumbnailAltText); }
        if ($this->fitStrategy !== Omitted::Value) { $out->{'fitStrategy'} = Shape::encode($this->fitStrategy); }
        if ($this->unalteredStillImageAsset !== Omitted::Value) { $out->{'unalteredStillImageAsset'} = Shape::encode($this->unalteredStillImageAsset); }
        if ($this->plugin !== Omitted::Value) { $out->{'plugin'} = Shape::encode($this->plugin); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
