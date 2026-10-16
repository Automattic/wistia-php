<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowAccessCustomizationsDataPrivate
{
    public function __construct(
        #[\SensitiveParameter] public string|null $password_protect_on,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['password_protect_on' => ['type' => 'string']]]);
        return new self(
            property_exists($value, 'password_protect_on') ? $value->{'password_protect_on'} : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['password_protect_on'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
