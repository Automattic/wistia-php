<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowLeadCaptureCustomizationsDataPluginRequireEmailV1
{
    public function __construct(
        #[\SensitiveParameter] public string|null $on,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string']]]);
        return new self(
            property_exists($value, 'on') ? $value->{'on'} : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['on'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
