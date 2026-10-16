<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowChaptersCustomizationsDataPluginChapters
{
    public function __construct(
        #[\SensitiveParameter] public string|null $on,
        #[\SensitiveParameter] public string|null $visibleOnLoad,
        /** @var list<ShowChaptersCustomizationsDataPluginChaptersChapterListItem>|null */
        #[\SensitiveParameter] public array|null $chapterList,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string'], 'visibleOnLoad' => ['type' => 'string'], 'chapterList' => ['type' => 'array', 'items' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['id' => ['type' => 'string'], 'title' => ['type' => 'string'], 'time' => ['type' => 'string'], 'deleted' => ['type' => 'string']]]]]]);
        return new self(
            property_exists($value, 'on') ? $value->{'on'} : null,
            property_exists($value, 'visibleOnLoad') ? $value->{'visibleOnLoad'} : null,
            property_exists($value, 'chapterList') ? array_map(static fn($v) => ShowChaptersCustomizationsDataPluginChaptersChapterListItem::fromWire($v), $value->{'chapterList'}) : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['on', 'visibleOnLoad', 'chapterList'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
