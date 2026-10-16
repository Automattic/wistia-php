<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowCustomizationsDataPluginChaptersChapterListItem
{
    public function __construct(
        #[\SensitiveParameter] public string|null $id,
        #[\SensitiveParameter] public string|null $title,
        #[\SensitiveParameter] public string|null $time,
        #[\SensitiveParameter] public string|null $deleted,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['id' => ['type' => 'string'], 'title' => ['type' => 'string'], 'time' => ['type' => 'string'], 'deleted' => ['type' => 'string']]]);
        return new self(
            property_exists($value, 'id') ? $value->{'id'} : null,
            property_exists($value, 'title') ? $value->{'title'} : null,
            property_exists($value, 'time') ? $value->{'time'} : null,
            property_exists($value, 'deleted') ? $value->{'deleted'} : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['id', 'title', 'time', 'deleted'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
