<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowEngagementCustomizationsDataPluginPostRollV1Style
{
    public function __construct(
        #[\SensitiveParameter] public string|null $backgroundColor,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['backgroundColor' => ['type' => 'string']]]);
        return new self(
            property_exists($value, 'backgroundColor') ? $value->{'backgroundColor'} : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['backgroundColor'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
