<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowRelatedMediaCustomizationsDataPluginRelatedMedia
{
    public function __construct(
        #[\SensitiveParameter] public string|null $on,
        /** @var list<string>|null */
        #[\SensitiveParameter] public array|null $hashedIdList,
        #[\SensitiveParameter] public string|null $shouldShowOnPause,
        #[\SensitiveParameter] public string|null $shouldShowOnEnd,
        #[\SensitiveParameter] public string|null $mediaLabelText,
        #[\SensitiveParameter] public string|null $watchButtonText,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string'], 'hashedIdList' => ['type' => 'array', 'items' => ['type' => 'string']], 'shouldShowOnPause' => ['type' => 'string'], 'shouldShowOnEnd' => ['type' => 'string'], 'mediaLabelText' => ['type' => 'string'], 'watchButtonText' => ['type' => 'string']]]);
        return new self(
            property_exists($value, 'on') ? $value->{'on'} : null,
            property_exists($value, 'hashedIdList') ? $value->{'hashedIdList'} : null,
            property_exists($value, 'shouldShowOnPause') ? $value->{'shouldShowOnPause'} : null,
            property_exists($value, 'shouldShowOnEnd') ? $value->{'shouldShowOnEnd'} : null,
            property_exists($value, 'mediaLabelText') ? $value->{'mediaLabelText'} : null,
            property_exists($value, 'watchButtonText') ? $value->{'watchButtonText'} : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['on', 'hashedIdList', 'shouldShowOnPause', 'shouldShowOnEnd', 'mediaLabelText', 'watchButtonText'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
