<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class CreateCustomizationsInputPlugin implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public CreateCustomizationsInputPluginVideoThumbnail|null|Omitted $videoThumbnail = Omitted::Value,
        #[\SensitiveParameter] public CreateCustomizationsInputPluginSocialbarV1|null|Omitted $socialbar_v1 = Omitted::Value,
        #[\SensitiveParameter] public CreateCustomizationsInputPluginChapters|null|Omitted $chapters = Omitted::Value,
        #[\SensitiveParameter] public CreateCustomizationsInputPluginPostRollV1|null|Omitted $postRoll_v1 = Omitted::Value,
        #[\SensitiveParameter] public CreateCustomizationsInputPluginCaptionsV1|null|Omitted $captions_v1 = Omitted::Value,
    ) {
        if ($this->videoThumbnail !== Omitted::Value) { Shape::check(Shape::encode($this->videoThumbnail), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['clickToPlayButton' => ['type' => 'boolean']]], false); }
        if ($this->socialbar_v1 !== Omitted::Value) { Shape::check(Shape::encode($this->socialbar_v1), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['buttons' => ['type' => 'string'], 'showTweetCount' => ['type' => 'boolean'], 'tweetText' => ['type' => 'string'], 'height' => ['type' => 'integer']]], false); }
        if ($this->chapters !== Omitted::Value) { Shape::check(Shape::encode($this->chapters), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['visibleOnLoad' => ['type' => 'boolean'], 'chapterList' => ['type' => 'array', 'items' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['id' => ['type' => 'string'], 'title' => ['type' => 'string'], 'time' => ['type' => 'string'], 'deleted' => ['type' => 'string']]]], 'on' => ['type' => 'boolean']]], false); }
        if ($this->postRoll_v1 !== Omitted::Value) { Shape::check(Shape::encode($this->postRoll_v1), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['rewatch' => ['type' => 'boolean'], 'text' => ['type' => 'string'], 'link' => ['type' => 'string'], 'time' => ['unevaluatedProperties' => false, 'oneOf' => [['type' => 'string'], ['type' => 'number']]], 'autoSize' => ['type' => 'boolean'], 'style' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['backgroundColor' => ['type' => 'string']]], 'ctaType' => ['type' => 'string', 'enum' => ['text', 'image', 'html']], 'altText' => ['type' => 'string'], 'on' => ['type' => 'boolean'], 'conversionOpportunityKey' => ['type' => 'string']]], false); }
        if ($this->captions_v1 !== Omitted::Value) { Shape::check(Shape::encode($this->captions_v1), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'onByDefault' => ['type' => 'boolean']]], false); }
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
