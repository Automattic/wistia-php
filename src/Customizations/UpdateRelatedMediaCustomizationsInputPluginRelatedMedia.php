<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateRelatedMediaCustomizationsInputPluginRelatedMedia implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public bool|null|Omitted $on = Omitted::Value,
        /** @var list<string>|null|Omitted */
        #[\SensitiveParameter] public array|null|Omitted $hashedIdList = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $shouldShowOnPause = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $shouldShowOnEnd = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $mediaLabelText = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $watchButtonText = Omitted::Value,
    ) {
        if ($this->on !== Omitted::Value && $this->on !== null) { Shape::check(Shape::encode($this->on), ['type' => 'boolean'], true); }
        if ($this->hashedIdList !== Omitted::Value && $this->hashedIdList !== null) { Shape::check(Shape::encode($this->hashedIdList), ['type' => 'array', 'items' => ['type' => 'string']], true); }
        if ($this->shouldShowOnPause !== Omitted::Value && $this->shouldShowOnPause !== null) { Shape::check(Shape::encode($this->shouldShowOnPause), ['type' => 'boolean'], true); }
        if ($this->shouldShowOnEnd !== Omitted::Value && $this->shouldShowOnEnd !== null) { Shape::check(Shape::encode($this->shouldShowOnEnd), ['type' => 'boolean'], true); }
        if ($this->mediaLabelText !== Omitted::Value && $this->mediaLabelText !== null) { Shape::check(Shape::encode($this->mediaLabelText), ['type' => 'string'], true); }
        if ($this->watchButtonText !== Omitted::Value && $this->watchButtonText !== null) { Shape::check(Shape::encode($this->watchButtonText), ['type' => 'string'], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->on !== Omitted::Value) { $out->{'on'} = Shape::encode($this->on); }
        if ($this->hashedIdList !== Omitted::Value) { $out->{'hashedIdList'} = Shape::encode($this->hashedIdList); }
        if ($this->shouldShowOnPause !== Omitted::Value) { $out->{'shouldShowOnPause'} = Shape::encode($this->shouldShowOnPause); }
        if ($this->shouldShowOnEnd !== Omitted::Value) { $out->{'shouldShowOnEnd'} = Shape::encode($this->shouldShowOnEnd); }
        if ($this->mediaLabelText !== Omitted::Value) { $out->{'mediaLabelText'} = Shape::encode($this->mediaLabelText); }
        if ($this->watchButtonText !== Omitted::Value) { $out->{'watchButtonText'} = Shape::encode($this->watchButtonText); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
