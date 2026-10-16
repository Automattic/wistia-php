<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateCustomizationsInputPlugin implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public UpdateCustomizationsInputPluginVideoThumbnail|null|Omitted $videoThumbnail = Omitted::Value,
        #[\SensitiveParameter] public UpdateCustomizationsInputPluginSocialbarV1|null|Omitted $socialbar_v1 = Omitted::Value,
        #[\SensitiveParameter] public UpdateCustomizationsInputPluginChapters|null|Omitted $chapters = Omitted::Value,
        #[\SensitiveParameter] public UpdateCustomizationsInputPluginPostRollV1|null|Omitted $postRoll_v1 = Omitted::Value,
        #[\SensitiveParameter] public UpdateCustomizationsInputPluginCaptionsV1|null|Omitted $captions_v1 = Omitted::Value,
    ) {
        if ($this->videoThumbnail !== Omitted::Value && $this->videoThumbnail !== null) { Shape::check(Shape::encode($this->videoThumbnail), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['clickToPlayButton' => ['type' => 'boolean']]], true); }
        if ($this->socialbar_v1 !== Omitted::Value && $this->socialbar_v1 !== null) { Shape::check(Shape::encode($this->socialbar_v1), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['buttons' => ['type' => 'string'], 'showTweetCount' => ['type' => 'boolean'], 'tweetText' => ['type' => 'string'], 'height' => ['type' => 'integer']]], true); }
        if ($this->chapters !== Omitted::Value && $this->chapters !== null) { Shape::check(Shape::encode($this->chapters), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['visibleOnLoad' => ['type' => 'boolean'], 'chapterList' => ['type' => 'array', 'items' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['id' => ['type' => 'string'], 'title' => ['type' => 'string'], 'time' => ['type' => 'string'], 'deleted' => ['type' => 'string']]]], 'on' => ['type' => 'boolean']]], true); }
        if ($this->postRoll_v1 !== Omitted::Value && $this->postRoll_v1 !== null) { Shape::check(Shape::encode($this->postRoll_v1), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['rewatch' => ['type' => 'boolean'], 'text' => ['type' => 'string'], 'link' => ['type' => 'string'], 'time' => ['unevaluatedProperties' => false, 'oneOf' => [['type' => 'string'], ['type' => 'number']]], 'autoSize' => ['type' => 'boolean'], 'style' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['backgroundColor' => ['type' => 'string']]], 'ctaType' => ['type' => 'string', 'enum' => ['text', 'image', 'html']], 'altText' => ['type' => 'string'], 'on' => ['type' => 'boolean'], 'conversionOpportunityKey' => ['type' => 'string']]], true); }
        if ($this->captions_v1 !== Omitted::Value && $this->captions_v1 !== null) { Shape::check(Shape::encode($this->captions_v1), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'onByDefault' => ['type' => 'boolean']]], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->videoThumbnail !== Omitted::Value) { $out->{'videoThumbnail'} = Shape::encode($this->videoThumbnail); }
        if ($this->socialbar_v1 !== Omitted::Value) { $out->{'socialbar-v1'} = Shape::encode($this->socialbar_v1); }
        if ($this->chapters !== Omitted::Value) { $out->{'chapters'} = Shape::encode($this->chapters); }
        if ($this->postRoll_v1 !== Omitted::Value) { $out->{'postRoll-v1'} = Shape::encode($this->postRoll_v1); }
        if ($this->captions_v1 !== Omitted::Value) { $out->{'captions-v1'} = Shape::encode($this->captions_v1); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
