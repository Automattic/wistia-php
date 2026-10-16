<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateAccessCustomizationsInputPluginPasswordProtectedVideo implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public bool|null|Omitted $on = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $challenge = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $src = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $async = Omitted::Value,
    ) {
        if ($this->on !== Omitted::Value && $this->on !== null) { Shape::check(Shape::encode($this->on), ['type' => 'boolean'], true); }
        if ($this->challenge !== Omitted::Value && $this->challenge !== null) { Shape::check(Shape::encode($this->challenge), ['type' => 'string'], true); }
        if ($this->src !== Omitted::Value && $this->src !== null) { Shape::check(Shape::encode($this->src), ['type' => 'string'], true); }
        if ($this->async !== Omitted::Value && $this->async !== null) { Shape::check(Shape::encode($this->async), ['type' => 'boolean'], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->on !== Omitted::Value) { $out->{'on'} = Shape::encode($this->on); }
        if ($this->challenge !== Omitted::Value) { $out->{'challenge'} = Shape::encode($this->challenge); }
        if ($this->src !== Omitted::Value) { $out->{'src'} = Shape::encode($this->src); }
        if ($this->async !== Omitted::Value) { $out->{'async'} = Shape::encode($this->async); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
