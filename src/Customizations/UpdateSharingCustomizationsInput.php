<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateSharingCustomizationsInput implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public UpdateSharingCustomizationsInputPlugin|null|Omitted $plugin = Omitted::Value,
    ) {
        if ($this->plugin !== Omitted::Value && $this->plugin !== null) { Shape::check(Shape::encode($this->plugin), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['share' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'channels' => ['type' => 'array', 'items' => ['type' => 'string', 'enum' => ['embed', 'email', 'download', 'facebook', 'twitter', 'linkedIn']]], 'tweetText' => ['type' => 'string'], 'downloadType' => ['type' => 'string', 'enum' => ['sd_mp4', 'hd_mp4', 'original', 'all_qualities']], 'overrideUrl' => ['type' => 'string'], 'pageUrl' => ['type' => 'string'], 'pageTitle' => ['type' => 'string'], 'conversionOpportunityKey' => ['type' => 'string']]]]], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->plugin !== Omitted::Value) { $out->{'plugin'} = Shape::encode($this->plugin); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
