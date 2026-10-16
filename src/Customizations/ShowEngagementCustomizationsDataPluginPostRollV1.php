<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowEngagementCustomizationsDataPluginPostRollV1
{
    public function __construct(
        #[\SensitiveParameter] public string|null $rewatch,
        #[\SensitiveParameter] public string|null $text,
        #[\SensitiveParameter] public string|null $link,
        #[\SensitiveParameter] public string|null $time,
        #[\SensitiveParameter] public string|null $autoSize,
        #[\SensitiveParameter] public ShowEngagementCustomizationsDataPluginPostRollV1Style|null $style,
        #[\SensitiveParameter] public string|null $ctaType,
        #[\SensitiveParameter] public string|null $on,
        #[\SensitiveParameter] public string|null $conversionOpportunityKey,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['rewatch' => ['type' => 'string'], 'text' => ['type' => 'string'], 'link' => ['type' => 'string'], 'time' => ['type' => 'string'], 'autoSize' => ['type' => 'string'], 'style' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['backgroundColor' => ['type' => 'string']]], 'ctaType' => ['type' => 'string'], 'on' => ['type' => 'string'], 'conversionOpportunityKey' => ['type' => 'string']]]);
        return new self(
            property_exists($value, 'rewatch') ? $value->{'rewatch'} : null,
            property_exists($value, 'text') ? $value->{'text'} : null,
            property_exists($value, 'link') ? $value->{'link'} : null,
            property_exists($value, 'time') ? $value->{'time'} : null,
            property_exists($value, 'autoSize') ? $value->{'autoSize'} : null,
            property_exists($value, 'style') ? ShowEngagementCustomizationsDataPluginPostRollV1Style::fromWire($value->{'style'}) : null,
            property_exists($value, 'ctaType') ? $value->{'ctaType'} : null,
            property_exists($value, 'on') ? $value->{'on'} : null,
            property_exists($value, 'conversionOpportunityKey') ? $value->{'conversionOpportunityKey'} : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['rewatch', 'text', 'link', 'time', 'autoSize', 'style', 'ctaType', 'on', 'conversionOpportunityKey'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
