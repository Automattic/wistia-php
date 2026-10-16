<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowAppearanceCustomizationsDataPlayerColorGradient
{
    public function __construct(
        #[\SensitiveParameter] public string|null $on,
        /** @var list<array>|null */
        #[\SensitiveParameter] public array|null $colors,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'string'], 'colors' => ['type' => 'array', 'items' => ['type' => 'array']]]]);
        return new self(
            property_exists($value, 'on') ? $value->{'on'} : null,
            property_exists($value, 'colors') ? $value->{'colors'} : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['on', 'colors'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
