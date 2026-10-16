<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class CreateCustomizationsInputPluginSocialbarV1 implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public string|null|Omitted $buttons = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $showTweetCount = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $tweetText = Omitted::Value,
        #[\SensitiveParameter] public int|null|Omitted $height = Omitted::Value,
    ) {
        if ($this->buttons !== Omitted::Value) { Shape::check(Shape::encode($this->buttons), ['type' => 'string'], false); }
        if ($this->showTweetCount !== Omitted::Value) { Shape::check(Shape::encode($this->showTweetCount), ['type' => 'boolean'], false); }
        if ($this->tweetText !== Omitted::Value) { Shape::check(Shape::encode($this->tweetText), ['type' => 'string'], false); }
        if ($this->height !== Omitted::Value) { Shape::check(Shape::encode($this->height), ['type' => 'integer'], false); }
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
