<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateCustomizationsInputPluginSocialbarV1 implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public string|null|Omitted $buttons = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $showTweetCount = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $tweetText = Omitted::Value,
        #[\SensitiveParameter] public int|null|Omitted $height = Omitted::Value,
    ) {
        if ($this->buttons !== Omitted::Value && $this->buttons !== null) { Shape::check(Shape::encode($this->buttons), ['type' => 'string'], true); }
        if ($this->showTweetCount !== Omitted::Value && $this->showTweetCount !== null) { Shape::check(Shape::encode($this->showTweetCount), ['type' => 'boolean'], true); }
        if ($this->tweetText !== Omitted::Value && $this->tweetText !== null) { Shape::check(Shape::encode($this->tweetText), ['type' => 'string'], true); }
        if ($this->height !== Omitted::Value && $this->height !== null) { Shape::check(Shape::encode($this->height), ['type' => 'integer'], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->buttons !== Omitted::Value) { $out->{'buttons'} = Shape::encode($this->buttons); }
        if ($this->showTweetCount !== Omitted::Value) { $out->{'showTweetCount'} = Shape::encode($this->showTweetCount); }
        if ($this->tweetText !== Omitted::Value) { $out->{'tweetText'} = Shape::encode($this->tweetText); }
        if ($this->height !== Omitted::Value) { $out->{'height'} = Shape::encode($this->height); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
