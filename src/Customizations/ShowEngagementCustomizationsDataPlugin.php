<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowEngagementCustomizationsDataPlugin
{
    public function __construct(
        #[\SensitiveParameter] public ShowEngagementCustomizationsDataPluginPostRollV1|null $postRoll_v1,
        #[\SensitiveParameter] public ShowEngagementCustomizationsDataPluginMidrollLinkV1|null $midrollLink_v1,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['postRoll-v1' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['rewatch' => ['type' => 'string'], 'text' => ['type' => 'string'], 'link' => ['type' => 'string'], 'time' => ['type' => 'string'], 'autoSize' => ['type' => 'string'], 'style' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['backgroundColor' => ['type' => 'string']]], 'ctaType' => ['type' => 'string'], 'on' => ['type' => 'string'], 'conversionOpportunityKey' => ['type' => 'string']]], 'midrollLink-v1' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string'], 'links' => ['type' => 'array', 'items' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['text' => ['type' => 'string'], 'url' => ['type' => 'string'], 'time' => ['type' => 'string'], 'duration' => ['type' => 'string'], 'conversionOpportunityKey' => ['type' => 'string']]]]]]]]);
        return new self(
            property_exists($value, 'postRoll-v1') ? ShowEngagementCustomizationsDataPluginPostRollV1::fromWire($value->{'postRoll-v1'}) : null,
            property_exists($value, 'midrollLink-v1') ? ShowEngagementCustomizationsDataPluginMidrollLinkV1::fromWire($value->{'midrollLink-v1'}) : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['postRoll-v1', 'midrollLink-v1'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
