<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateEngagementCustomizationsInputPluginMidrollLinkV1 implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public bool|null|Omitted $on = Omitted::Value,
        /** @var list<UpdateEngagementCustomizationsInputPluginMidrollLinkV1LinksItem>|null|Omitted */
        #[\SensitiveParameter] public array|null|Omitted $links = Omitted::Value,
    ) {
        if ($this->on !== Omitted::Value && $this->on !== null) { Shape::check(Shape::encode($this->on), ['type' => 'boolean'], true); }
        if ($this->links !== Omitted::Value && $this->links !== null) { Shape::check(Shape::encode($this->links), ['type' => 'array', 'items' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['text' => ['type' => 'string'], 'url' => ['type' => 'string'], 'time' => ['type' => 'string'], 'duration' => ['type' => 'string']]]], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->on !== Omitted::Value) { $out->{'on'} = Shape::encode($this->on); }
        if ($this->links !== Omitted::Value) { $out->{'links'} = Shape::encode($this->links); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
