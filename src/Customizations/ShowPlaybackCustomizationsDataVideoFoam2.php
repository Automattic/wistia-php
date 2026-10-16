<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowPlaybackCustomizationsDataVideoFoam2
{
    public function __construct(
        #[\SensitiveParameter] public string|null $minWidth,
        #[\SensitiveParameter] public string|null $maxWidth,
        #[\SensitiveParameter] public string|null $minHeight,
        #[\SensitiveParameter] public string|null $maxHeight,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['type' => 'object', 'unevaluatedProperties' => false, 'properties' => ['minWidth' => ['type' => 'string'], 'maxWidth' => ['type' => 'string'], 'minHeight' => ['type' => 'string'], 'maxHeight' => ['type' => 'string']]]);
        return new self(
            property_exists($value, 'minWidth') ? $value->{'minWidth'} : null,
            property_exists($value, 'maxWidth') ? $value->{'maxWidth'} : null,
            property_exists($value, 'minHeight') ? $value->{'minHeight'} : null,
            property_exists($value, 'maxHeight') ? $value->{'maxHeight'} : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['minWidth', 'maxWidth', 'minHeight', 'maxHeight'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
