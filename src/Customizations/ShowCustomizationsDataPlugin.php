<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowCustomizationsDataPlugin
{
    public function __construct(
        #[\SensitiveParameter] public ShowCustomizationsDataPluginPasswordProtectedVideo|null $passwordProtectedVideo,
        #[\SensitiveParameter] public ShowCustomizationsDataPluginVideoThumbnail|null $videoThumbnail,
        #[\SensitiveParameter] public ShowCustomizationsDataPluginSocialbarV1|null $socialbar_v1,
        #[\SensitiveParameter] public ShowCustomizationsDataPluginChapters|null $chapters,
        #[\SensitiveParameter] public ShowCustomizationsDataPluginPostRollV1|null $postRoll_v1,
        #[\SensitiveParameter] public ShowCustomizationsDataPluginCaptionsV1|null $captions_v1,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['passwordProtectedVideo' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'string'], 'src' => ['type' => 'string'], 'challenge' => ['type' => 'string'], 'async' => ['type' => 'string']]], 'videoThumbnail' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['clickToPlayButton' => ['type' => 'string']]], 'socialbar-v1' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['buttons' => ['type' => 'string'], 'showTweetCount' => ['type' => 'string'], 'tweetText' => ['type' => 'string'], 'height' => ['type' => 'string']]], 'chapters' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['visibleOnLoad' => ['type' => 'string'], 'chapterList' => ['type' => 'array', 'items' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['id' => ['type' => 'string'], 'title' => ['type' => 'string'], 'time' => ['type' => 'string'], 'deleted' => ['type' => 'string']]]], 'on' => ['type' => 'string']]], 'postRoll-v1' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['rewatch' => ['type' => 'string'], 'text' => ['type' => 'string'], 'link' => ['type' => 'string'], 'time' => ['type' => 'string'], 'autoSize' => ['type' => 'string'], 'style' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['backgroundColor' => ['type' => 'string']]], 'ctaType' => ['type' => 'string'], 'altText' => ['type' => 'string'], 'on' => ['type' => 'string'], 'conversionOpportunityKey' => ['type' => 'string']]], 'captions-v1' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'string'], 'onByDefault' => ['type' => 'string']]]]]);
        return new self(
            property_exists($value, 'passwordProtectedVideo') ? ShowCustomizationsDataPluginPasswordProtectedVideo::fromWire($value->{'passwordProtectedVideo'}) : null,
            property_exists($value, 'videoThumbnail') ? ShowCustomizationsDataPluginVideoThumbnail::fromWire($value->{'videoThumbnail'}) : null,
            property_exists($value, 'socialbar-v1') ? ShowCustomizationsDataPluginSocialbarV1::fromWire($value->{'socialbar-v1'}) : null,
            property_exists($value, 'chapters') ? ShowCustomizationsDataPluginChapters::fromWire($value->{'chapters'}) : null,
            property_exists($value, 'postRoll-v1') ? ShowCustomizationsDataPluginPostRollV1::fromWire($value->{'postRoll-v1'}) : null,
            property_exists($value, 'captions-v1') ? ShowCustomizationsDataPluginCaptionsV1::fromWire($value->{'captions-v1'}) : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['passwordProtectedVideo', 'videoThumbnail', 'socialbar-v1', 'chapters', 'postRoll-v1', 'captions-v1'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
