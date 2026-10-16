<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowThumbnailCustomizationsDataPlugin
{
    public function __construct(
        #[\SensitiveParameter] public ShowThumbnailCustomizationsDataPluginVideoThumbnail|null $videoThumbnail,
        #[\SensitiveParameter] public ShowThumbnailCustomizationsDataPluginThumbnailTextOverlayV2|null $thumbnailTextOverlay_v2,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['videoThumbnail' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['clickToPlayButton' => ['type' => 'string'], 'clickForSound' => ['type' => 'string'], 'hashedId' => ['type' => 'string'], 'trimStart' => ['type' => 'string'], 'trimEnd' => ['type' => 'string'], 'priorityMode' => ['type' => 'string']]], 'thumbnailTextOverlay-v2' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string'], 'text' => ['type' => 'string']]]]]);
        return new self(
            property_exists($value, 'videoThumbnail') ? ShowThumbnailCustomizationsDataPluginVideoThumbnail::fromWire($value->{'videoThumbnail'}) : null,
            property_exists($value, 'thumbnailTextOverlay-v2') ? ShowThumbnailCustomizationsDataPluginThumbnailTextOverlayV2::fromWire($value->{'thumbnailTextOverlay-v2'}) : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['videoThumbnail', 'thumbnailTextOverlay-v2'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
