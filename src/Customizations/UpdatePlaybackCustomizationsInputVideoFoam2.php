<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdatePlaybackCustomizationsInputVideoFoam2 implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public int|null|Omitted $minWidth = Omitted::Value,
        #[\SensitiveParameter] public int|null|Omitted $maxWidth = Omitted::Value,
        #[\SensitiveParameter] public int|null|Omitted $minHeight = Omitted::Value,
        #[\SensitiveParameter] public int|null|Omitted $maxHeight = Omitted::Value,
    ) {
        if ($this->minWidth !== Omitted::Value && $this->minWidth !== null) { Shape::check(Shape::encode($this->minWidth), ['type' => 'integer'], true); }
        if ($this->maxWidth !== Omitted::Value && $this->maxWidth !== null) { Shape::check(Shape::encode($this->maxWidth), ['type' => 'integer'], true); }
        if ($this->minHeight !== Omitted::Value && $this->minHeight !== null) { Shape::check(Shape::encode($this->minHeight), ['type' => 'integer'], true); }
        if ($this->maxHeight !== Omitted::Value && $this->maxHeight !== null) { Shape::check(Shape::encode($this->maxHeight), ['type' => 'integer'], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->minWidth !== Omitted::Value) { $out->{'minWidth'} = Shape::encode($this->minWidth); }
        if ($this->maxWidth !== Omitted::Value) { $out->{'maxWidth'} = Shape::encode($this->maxWidth); }
        if ($this->minHeight !== Omitted::Value) { $out->{'minHeight'} = Shape::encode($this->minHeight); }
        if ($this->maxHeight !== Omitted::Value) { $out->{'maxHeight'} = Shape::encode($this->maxHeight); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
