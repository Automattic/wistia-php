<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowRelatedMediaCustomizationsDataPlugin
{
    public function __construct(
        #[\SensitiveParameter] public ShowRelatedMediaCustomizationsDataPluginRelatedMedia|null $relatedMedia,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['relatedMedia' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string'], 'hashedIdList' => ['type' => 'array', 'items' => ['type' => 'string']], 'shouldShowOnPause' => ['type' => 'string'], 'shouldShowOnEnd' => ['type' => 'string'], 'mediaLabelText' => ['type' => 'string'], 'watchButtonText' => ['type' => 'string']]]]]);
        return new self(
            property_exists($value, 'relatedMedia') ? ShowRelatedMediaCustomizationsDataPluginRelatedMedia::fromWire($value->{'relatedMedia'}) : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['relatedMedia'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
