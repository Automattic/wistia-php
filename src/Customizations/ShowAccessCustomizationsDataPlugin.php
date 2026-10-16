<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowAccessCustomizationsDataPlugin
{
    public function __construct(
        #[\SensitiveParameter] public ShowAccessCustomizationsDataPluginPasswordProtectedVideo|null $passwordProtectedVideo,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['passwordProtectedVideo' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string'], 'challenge' => ['type' => 'string'], 'src' => ['type' => 'string'], 'async' => ['type' => 'string']]]]]);
        return new self(
            property_exists($value, 'passwordProtectedVideo') ? ShowAccessCustomizationsDataPluginPasswordProtectedVideo::fromWire($value->{'passwordProtectedVideo'}) : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['passwordProtectedVideo'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
