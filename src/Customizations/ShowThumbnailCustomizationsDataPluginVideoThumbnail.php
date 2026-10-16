<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowThumbnailCustomizationsDataPluginVideoThumbnail
{
    public function __construct(
        #[\SensitiveParameter] public string|null $clickToPlayButton,
        #[\SensitiveParameter] public string|null $clickForSound,
        #[\SensitiveParameter] public string|null $hashedId,
        #[\SensitiveParameter] public string|null $trimStart,
        #[\SensitiveParameter] public string|null $trimEnd,
        #[\SensitiveParameter] public string|null $priorityMode,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['clickToPlayButton' => ['type' => 'string'], 'clickForSound' => ['type' => 'string'], 'hashedId' => ['type' => 'string'], 'trimStart' => ['type' => 'string'], 'trimEnd' => ['type' => 'string'], 'priorityMode' => ['type' => 'string']]]);
        return new self(
            property_exists($value, 'clickToPlayButton') ? $value->{'clickToPlayButton'} : null,
            property_exists($value, 'clickForSound') ? $value->{'clickForSound'} : null,
            property_exists($value, 'hashedId') ? $value->{'hashedId'} : null,
            property_exists($value, 'trimStart') ? $value->{'trimStart'} : null,
            property_exists($value, 'trimEnd') ? $value->{'trimEnd'} : null,
            property_exists($value, 'priorityMode') ? $value->{'priorityMode'} : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['clickToPlayButton', 'clickForSound', 'hashedId', 'trimStart', 'trimEnd', 'priorityMode'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
