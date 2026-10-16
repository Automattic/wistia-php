<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateAccessibilityCustomizationsInputPluginExtendedAudioDescription implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public bool|null|Omitted $on = Omitted::Value,
    ) {
        if ($this->on !== Omitted::Value && $this->on !== null) { Shape::check(Shape::encode($this->on), ['type' => 'boolean'], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->on !== Omitted::Value) { $out->{'on'} = Shape::encode($this->on); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
