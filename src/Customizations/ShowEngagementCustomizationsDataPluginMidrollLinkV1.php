<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowEngagementCustomizationsDataPluginMidrollLinkV1
{
    public function __construct(
        #[\SensitiveParameter] public string|null $on,
        /** @var list<ShowEngagementCustomizationsDataPluginMidrollLinkV1LinksItem>|null */
        #[\SensitiveParameter] public array|null $links,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string'], 'links' => ['type' => 'array', 'items' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['text' => ['type' => 'string'], 'url' => ['type' => 'string'], 'time' => ['type' => 'string'], 'duration' => ['type' => 'string'], 'conversionOpportunityKey' => ['type' => 'string']]]]]]);
        return new self(
            property_exists($value, 'on') ? $value->{'on'} : null,
            property_exists($value, 'links') ? array_map(static fn($v) => ShowEngagementCustomizationsDataPluginMidrollLinkV1LinksItem::fromWire($v), $value->{'links'}) : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['on', 'links'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
