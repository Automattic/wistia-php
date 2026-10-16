<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowSharingCustomizationsDataPlugin
{
    public function __construct(
        #[\SensitiveParameter] public ShowSharingCustomizationsDataPluginShare|null $share,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['share' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string'], 'channels' => ['type' => 'array', 'items' => ['type' => 'string']], 'tweetText' => ['type' => 'string'], 'downloadType' => ['type' => 'string'], 'overrideUrl' => ['type' => 'string'], 'pageUrl' => ['type' => 'string'], 'pageTitle' => ['type' => 'string'], 'conversionOpportunityKey' => ['type' => 'string']]]]]);
        return new self(
            property_exists($value, 'share') ? ShowSharingCustomizationsDataPluginShare::fromWire($value->{'share'}) : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['share'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
