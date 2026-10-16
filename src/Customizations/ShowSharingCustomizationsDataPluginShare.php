<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowSharingCustomizationsDataPluginShare
{
    public function __construct(
        #[\SensitiveParameter] public string|null $on,
        /** @var list<string>|null */
        #[\SensitiveParameter] public array|null $channels,
        #[\SensitiveParameter] public string|null $tweetText,
        #[\SensitiveParameter] public string|null $downloadType,
        #[\SensitiveParameter] public string|null $overrideUrl,
        #[\SensitiveParameter] public string|null $pageUrl,
        #[\SensitiveParameter] public string|null $pageTitle,
        #[\SensitiveParameter] public string|null $conversionOpportunityKey,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string'], 'channels' => ['type' => 'array', 'items' => ['type' => 'string']], 'tweetText' => ['type' => 'string'], 'downloadType' => ['type' => 'string'], 'overrideUrl' => ['type' => 'string'], 'pageUrl' => ['type' => 'string'], 'pageTitle' => ['type' => 'string'], 'conversionOpportunityKey' => ['type' => 'string']]]);
        return new self(
            property_exists($value, 'on') ? $value->{'on'} : null,
            property_exists($value, 'channels') ? $value->{'channels'} : null,
            property_exists($value, 'tweetText') ? $value->{'tweetText'} : null,
            property_exists($value, 'downloadType') ? $value->{'downloadType'} : null,
            property_exists($value, 'overrideUrl') ? $value->{'overrideUrl'} : null,
            property_exists($value, 'pageUrl') ? $value->{'pageUrl'} : null,
            property_exists($value, 'pageTitle') ? $value->{'pageTitle'} : null,
            property_exists($value, 'conversionOpportunityKey') ? $value->{'conversionOpportunityKey'} : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['on', 'channels', 'tweetText', 'downloadType', 'overrideUrl', 'pageUrl', 'pageTitle', 'conversionOpportunityKey'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
