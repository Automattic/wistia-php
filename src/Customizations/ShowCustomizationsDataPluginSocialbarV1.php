<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowCustomizationsDataPluginSocialbarV1
{
    public function __construct(
        #[\SensitiveParameter] public string|null $buttons,
        #[\SensitiveParameter] public string|null $showTweetCount,
        #[\SensitiveParameter] public string|null $tweetText,
        #[\SensitiveParameter] public string|null $height,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['buttons' => ['type' => 'string'], 'showTweetCount' => ['type' => 'string'], 'tweetText' => ['type' => 'string'], 'height' => ['type' => 'string']]]);
        return new self(
            property_exists($value, 'buttons') ? $value->{'buttons'} : null,
            property_exists($value, 'showTweetCount') ? $value->{'showTweetCount'} : null,
            property_exists($value, 'tweetText') ? $value->{'tweetText'} : null,
            property_exists($value, 'height') ? $value->{'height'} : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['buttons', 'showTweetCount', 'tweetText', 'height'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
