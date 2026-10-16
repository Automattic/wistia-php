<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateThumbnailCustomizationsInputPluginThumbnailTextOverlayV2 implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public bool|null|Omitted $on = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $text = Omitted::Value,
    ) {
        if ($this->on !== Omitted::Value && $this->on !== null) { Shape::check(Shape::encode($this->on), ['type' => 'boolean'], true); }
        if ($this->text !== Omitted::Value && $this->text !== null) { Shape::check(Shape::encode($this->text), ['type' => 'string'], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->on !== Omitted::Value) { $out->{'on'} = Shape::encode($this->on); }
        if ($this->text !== Omitted::Value) { $out->{'text'} = Shape::encode($this->text); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
