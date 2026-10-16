<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateSharingCustomizationsInputPluginShare implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public bool|null|Omitted $on = Omitted::Value,
        /** @var list<string>|null|Omitted */
        #[\SensitiveParameter] public array|null|Omitted $channels = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $tweetText = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $downloadType = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $overrideUrl = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $pageUrl = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $pageTitle = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $conversionOpportunityKey = Omitted::Value,
    ) {
        if ($this->on !== Omitted::Value && $this->on !== null) { Shape::check(Shape::encode($this->on), ['type' => 'boolean'], true); }
        if ($this->channels !== Omitted::Value && $this->channels !== null) { Shape::check(Shape::encode($this->channels), ['type' => 'array', 'items' => ['type' => 'string', 'enum' => ['embed', 'email', 'download', 'facebook', 'twitter', 'linkedIn']]], true); }
        if ($this->tweetText !== Omitted::Value && $this->tweetText !== null) { Shape::check(Shape::encode($this->tweetText), ['type' => 'string'], true); }
        if ($this->downloadType !== Omitted::Value && $this->downloadType !== null) { Shape::check(Shape::encode($this->downloadType), ['type' => 'string', 'enum' => ['sd_mp4', 'hd_mp4', 'original', 'all_qualities']], true); }
        if ($this->overrideUrl !== Omitted::Value && $this->overrideUrl !== null) { Shape::check(Shape::encode($this->overrideUrl), ['type' => 'string'], true); }
        if ($this->pageUrl !== Omitted::Value && $this->pageUrl !== null) { Shape::check(Shape::encode($this->pageUrl), ['type' => 'string'], true); }
        if ($this->pageTitle !== Omitted::Value && $this->pageTitle !== null) { Shape::check(Shape::encode($this->pageTitle), ['type' => 'string'], true); }
        if ($this->conversionOpportunityKey !== Omitted::Value && $this->conversionOpportunityKey !== null) { Shape::check(Shape::encode($this->conversionOpportunityKey), ['type' => 'string'], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->on !== Omitted::Value) { $out->{'on'} = Shape::encode($this->on); }
        if ($this->channels !== Omitted::Value) { $out->{'channels'} = Shape::encode($this->channels); }
        if ($this->tweetText !== Omitted::Value) { $out->{'tweetText'} = Shape::encode($this->tweetText); }
        if ($this->downloadType !== Omitted::Value) { $out->{'downloadType'} = Shape::encode($this->downloadType); }
        if ($this->overrideUrl !== Omitted::Value) { $out->{'overrideUrl'} = Shape::encode($this->overrideUrl); }
        if ($this->pageUrl !== Omitted::Value) { $out->{'pageUrl'} = Shape::encode($this->pageUrl); }
        if ($this->pageTitle !== Omitted::Value) { $out->{'pageTitle'} = Shape::encode($this->pageTitle); }
        if ($this->conversionOpportunityKey !== Omitted::Value) { $out->{'conversionOpportunityKey'} = Shape::encode($this->conversionOpportunityKey); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
