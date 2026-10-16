<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateAppearanceCustomizationsInputPlayerColorGradient implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public bool|null|Omitted $on = Omitted::Value,
        /** @var list<array{string, int|float}>|null|Omitted */
        #[\SensitiveParameter] public array|null|Omitted $colors = Omitted::Value,
    ) {
        if ($this->on !== Omitted::Value && $this->on !== null) { Shape::check(Shape::encode($this->on), ['type' => 'boolean'], true); }
        if ($this->colors !== Omitted::Value && $this->colors !== null) {
            Shape::check($this->colors, ['type' => 'array', 'items' => ['type' => 'array']], true);
            Shape::gradient($this->colors);
        }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->on !== Omitted::Value) { $out->{'on'} = Shape::encode($this->on); }
        if ($this->colors !== Omitted::Value) { $out->{'colors'} = Shape::encode($this->colors); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
