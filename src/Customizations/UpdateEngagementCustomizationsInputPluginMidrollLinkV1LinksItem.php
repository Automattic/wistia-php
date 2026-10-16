<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateEngagementCustomizationsInputPluginMidrollLinkV1LinksItem implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public string|null|Omitted $text = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $url = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $time = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $duration = Omitted::Value,
    ) {
        if ($this->text !== Omitted::Value && $this->text !== null) { Shape::check(Shape::encode($this->text), ['type' => 'string'], true); }
        if ($this->url !== Omitted::Value && $this->url !== null) { Shape::check(Shape::encode($this->url), ['type' => 'string'], true); }
        if ($this->time !== Omitted::Value && $this->time !== null) { Shape::check(Shape::encode($this->time), ['type' => 'string'], true); }
        if ($this->duration !== Omitted::Value && $this->duration !== null) { Shape::check(Shape::encode($this->duration), ['type' => 'string'], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->text !== Omitted::Value) { $out->{'text'} = Shape::encode($this->text); }
        if ($this->url !== Omitted::Value) { $out->{'url'} = Shape::encode($this->url); }
        if ($this->time !== Omitted::Value) { $out->{'time'} = Shape::encode($this->time); }
        if ($this->duration !== Omitted::Value) { $out->{'duration'} = Shape::encode($this->duration); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
