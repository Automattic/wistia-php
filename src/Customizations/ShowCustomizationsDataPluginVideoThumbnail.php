<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowCustomizationsDataPluginVideoThumbnail
{
    public function __construct(
        #[\SensitiveParameter] public string|null $clickToPlayButton,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['clickToPlayButton' => ['type' => 'string']]]);
        return new self(
            property_exists($value, 'clickToPlayButton') ? $value->{'clickToPlayButton'} : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['clickToPlayButton'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
