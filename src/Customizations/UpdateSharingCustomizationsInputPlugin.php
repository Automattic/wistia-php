<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateSharingCustomizationsInputPlugin implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public UpdateSharingCustomizationsInputPluginShare|null|Omitted $share = Omitted::Value,
    ) {
        if ($this->share !== Omitted::Value && $this->share !== null) { Shape::check(Shape::encode($this->share), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'channels' => ['type' => 'array', 'items' => ['type' => 'string', 'enum' => ['embed', 'email', 'download', 'facebook', 'twitter', 'linkedIn']]], 'tweetText' => ['type' => 'string'], 'downloadType' => ['type' => 'string', 'enum' => ['sd_mp4', 'hd_mp4', 'original', 'all_qualities']], 'overrideUrl' => ['type' => 'string'], 'pageUrl' => ['type' => 'string'], 'pageTitle' => ['type' => 'string'], 'conversionOpportunityKey' => ['type' => 'string']]], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->share !== Omitted::Value) { $out->{'share'} = Shape::encode($this->share); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
