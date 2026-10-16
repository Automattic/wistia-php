<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowAccessCustomizationsData
{
    public function __construct(
        #[\SensitiveParameter] public ShowAccessCustomizationsDataPrivate|null $private,
        #[\SensitiveParameter] public ShowAccessCustomizationsDataEncrypted|null $encrypted,
        #[\SensitiveParameter] public ShowAccessCustomizationsDataPlugin|null $plugin,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['private' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['password_protect_on' => ['type' => 'string']]], 'encrypted' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['password_protect_password' => ['type' => 'string']]], 'plugin' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['passwordProtectedVideo' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string'], 'challenge' => ['type' => 'string'], 'src' => ['type' => 'string'], 'async' => ['type' => 'string']]]]]]]);
        return new self(
            property_exists($value, 'private') ? ShowAccessCustomizationsDataPrivate::fromWire($value->{'private'}) : null,
            property_exists($value, 'encrypted') ? ShowAccessCustomizationsDataEncrypted::fromWire($value->{'encrypted'}) : null,
            property_exists($value, 'plugin') ? ShowAccessCustomizationsDataPlugin::fromWire($value->{'plugin'}) : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['private', 'encrypted', 'plugin'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
