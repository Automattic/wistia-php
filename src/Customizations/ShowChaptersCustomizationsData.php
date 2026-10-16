<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowChaptersCustomizationsData
{
    public function __construct(
        #[\SensitiveParameter] public ShowChaptersCustomizationsDataPlugin|null $plugin,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['plugin' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['chapters' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string'], 'visibleOnLoad' => ['type' => 'string'], 'chapterList' => ['type' => 'array', 'items' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['id' => ['type' => 'string'], 'title' => ['type' => 'string'], 'time' => ['type' => 'string'], 'deleted' => ['type' => 'string']]]]]], 'audioChapters' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string'], 'visibleOnLoad' => ['type' => 'string'], 'chapterList' => ['type' => 'array', 'items' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['id' => ['type' => 'string'], 'title' => ['type' => 'string'], 'time' => ['type' => 'string'], 'deleted' => ['type' => 'string']]]]]]]]]]);
        return new self(
            property_exists($value, 'plugin') ? ShowChaptersCustomizationsDataPlugin::fromWire($value->{'plugin'}) : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['plugin'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
