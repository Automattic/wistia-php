<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowThumbnailCustomizationsData
{
    public function __construct(
        #[\SensitiveParameter] public string|null $stillUrl,
        #[\SensitiveParameter] public string|null $thumbnailAltText,
        #[\SensitiveParameter] public string|null $fitStrategy,
        #[\SensitiveParameter] public string|\stdClass|null $unalteredStillImageAsset,
        #[\SensitiveParameter] public ShowThumbnailCustomizationsDataPlugin|null $plugin,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['stillUrl' => ['type' => 'string'], 'thumbnailAltText' => ['type' => 'string'], 'fitStrategy' => ['type' => 'string'], 'unalteredStillImageAsset' => ['unevaluatedProperties' => false, 'oneOf' => [['type' => 'string'], ['type' => 'object', 'unevaluatedProperties' => true]]], 'plugin' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['videoThumbnail' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['clickToPlayButton' => ['type' => 'string'], 'clickForSound' => ['type' => 'string'], 'hashedId' => ['type' => 'string'], 'trimStart' => ['type' => 'string'], 'trimEnd' => ['type' => 'string'], 'priorityMode' => ['type' => 'string']]], 'thumbnailTextOverlay-v2' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string'], 'text' => ['type' => 'string']]]]]]]);
        return new self(
            property_exists($value, 'stillUrl') ? $value->{'stillUrl'} : null,
            property_exists($value, 'thumbnailAltText') ? $value->{'thumbnailAltText'} : null,
            property_exists($value, 'fitStrategy') ? $value->{'fitStrategy'} : null,
            property_exists($value, 'unalteredStillImageAsset') ? $value->{'unalteredStillImageAsset'} : null,
            property_exists($value, 'plugin') ? ShowThumbnailCustomizationsDataPlugin::fromWire($value->{'plugin'}) : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['stillUrl', 'thumbnailAltText', 'fitStrategy', 'unalteredStillImageAsset', 'plugin'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
