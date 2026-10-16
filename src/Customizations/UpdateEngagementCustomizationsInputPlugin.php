<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateEngagementCustomizationsInputPlugin implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public UpdateEngagementCustomizationsInputPluginPostRollV1|null|Omitted $postRoll_v1 = Omitted::Value,
        #[\SensitiveParameter] public UpdateEngagementCustomizationsInputPluginMidrollLinkV1|null|Omitted $midrollLink_v1 = Omitted::Value,
    ) {
        if ($this->postRoll_v1 !== Omitted::Value && $this->postRoll_v1 !== null) { Shape::check(Shape::encode($this->postRoll_v1), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['rewatch' => ['type' => 'boolean'], 'text' => ['type' => 'string'], 'link' => ['type' => 'string'], 'time' => ['unevaluatedProperties' => false, 'oneOf' => [['type' => 'string'], ['type' => 'number']]], 'autoSize' => ['type' => 'boolean'], 'style' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['backgroundColor' => ['type' => 'string']]], 'ctaType' => ['type' => 'string', 'enum' => ['text', 'image', 'html']], 'altText' => ['type' => 'string'], 'on' => ['type' => 'boolean'], 'conversionOpportunityKey' => ['type' => 'string']]], true); }
        if ($this->midrollLink_v1 !== Omitted::Value && $this->midrollLink_v1 !== null) { Shape::check(Shape::encode($this->midrollLink_v1), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'links' => ['type' => 'array', 'items' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['text' => ['type' => 'string'], 'url' => ['type' => 'string'], 'time' => ['type' => 'string'], 'duration' => ['type' => 'string']]]]]], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->postRoll_v1 !== Omitted::Value) { $out->{'postRoll-v1'} = Shape::encode($this->postRoll_v1); }
        if ($this->midrollLink_v1 !== Omitted::Value) { $out->{'midrollLink-v1'} = Shape::encode($this->midrollLink_v1); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
