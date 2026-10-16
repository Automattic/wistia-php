<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateAccessibilityCustomizationsInputPluginCaptions implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public bool|null|Omitted $on = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $onByDefault = Omitted::Value,
    ) {
        if ($this->on !== Omitted::Value && $this->on !== null) { Shape::check(Shape::encode($this->on), ['type' => 'boolean'], true); }
        if ($this->onByDefault !== Omitted::Value && $this->onByDefault !== null) { Shape::check(Shape::encode($this->onByDefault), ['type' => 'boolean'], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->on !== Omitted::Value) { $out->{'on'} = Shape::encode($this->on); }
        if ($this->onByDefault !== Omitted::Value) { $out->{'onByDefault'} = Shape::encode($this->onByDefault); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
