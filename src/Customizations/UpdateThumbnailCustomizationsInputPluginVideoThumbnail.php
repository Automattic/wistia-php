<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateThumbnailCustomizationsInputPluginVideoThumbnail implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public bool|null|Omitted $clickToPlayButton = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $clickForSound = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $hashedId = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $trimStart = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $trimEnd = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $priorityMode = Omitted::Value,
    ) {
        if ($this->clickToPlayButton !== Omitted::Value && $this->clickToPlayButton !== null) { Shape::check(Shape::encode($this->clickToPlayButton), ['type' => 'boolean'], true); }
        if ($this->clickForSound !== Omitted::Value && $this->clickForSound !== null) { Shape::check(Shape::encode($this->clickForSound), ['type' => 'boolean'], true); }
        if ($this->hashedId !== Omitted::Value && $this->hashedId !== null) { Shape::check(Shape::encode($this->hashedId), ['type' => 'string'], true); }
        if ($this->trimStart !== Omitted::Value && $this->trimStart !== null) { Shape::check(Shape::encode($this->trimStart), ['type' => 'string'], true); }
        if ($this->trimEnd !== Omitted::Value && $this->trimEnd !== null) { Shape::check(Shape::encode($this->trimEnd), ['type' => 'string'], true); }
        if ($this->priorityMode !== Omitted::Value && $this->priorityMode !== null) { Shape::check(Shape::encode($this->priorityMode), ['type' => 'string'], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->clickToPlayButton !== Omitted::Value) { $out->{'clickToPlayButton'} = Shape::encode($this->clickToPlayButton); }
        if ($this->clickForSound !== Omitted::Value) { $out->{'clickForSound'} = Shape::encode($this->clickForSound); }
        if ($this->hashedId !== Omitted::Value) { $out->{'hashedId'} = Shape::encode($this->hashedId); }
        if ($this->trimStart !== Omitted::Value) { $out->{'trimStart'} = Shape::encode($this->trimStart); }
        if ($this->trimEnd !== Omitted::Value) { $out->{'trimEnd'} = Shape::encode($this->trimEnd); }
        if ($this->priorityMode !== Omitted::Value) { $out->{'priorityMode'} = Shape::encode($this->priorityMode); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
