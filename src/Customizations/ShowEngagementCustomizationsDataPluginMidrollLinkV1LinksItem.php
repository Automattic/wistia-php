<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowEngagementCustomizationsDataPluginMidrollLinkV1LinksItem
{
    public function __construct(
        #[\SensitiveParameter] public string|null $text,
        #[\SensitiveParameter] public string|null $url,
        #[\SensitiveParameter] public string|null $time,
        #[\SensitiveParameter] public string|null $duration,
        #[\SensitiveParameter] public string|null $conversionOpportunityKey,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['text' => ['type' => 'string'], 'url' => ['type' => 'string'], 'time' => ['type' => 'string'], 'duration' => ['type' => 'string'], 'conversionOpportunityKey' => ['type' => 'string']]]);
        return new self(
            property_exists($value, 'text') ? $value->{'text'} : null,
            property_exists($value, 'url') ? $value->{'url'} : null,
            property_exists($value, 'time') ? $value->{'time'} : null,
            property_exists($value, 'duration') ? $value->{'duration'} : null,
            property_exists($value, 'conversionOpportunityKey') ? $value->{'conversionOpportunityKey'} : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['text', 'url', 'time', 'duration', 'conversionOpportunityKey'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
