<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowLeadCaptureCustomizationsData
{
    public function __construct(
        #[\SensitiveParameter] public ShowLeadCaptureCustomizationsDataPlugin|null $plugin,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['plugin' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['requireEmail-v1' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string']]], 'form' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string']]], 'hubspotForm' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string']]], 'marketoForm' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string']]], 'pardotForm' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string']]]]]]]);
        return new self(
            property_exists($value, 'plugin') ? ShowLeadCaptureCustomizationsDataPlugin::fromWire($value->{'plugin'}) : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['plugin'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
