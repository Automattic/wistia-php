<?php
declare(strict_types=1);

namespace Automattic\Wistia\Sharing;

use Automattic\Wistia\Media\Wire;

final readonly class SearchEnvelopeDataMediasItemCustomMetadataFieldValuesItem
{
    public function __construct(
        #[\SensitiveParameter] public string|null $key,
        #[\SensitiveParameter] public string|null $field_type,
        #[\SensitiveParameter] public string|float|bool|array|ContactReference|null $value,
        #[\SensitiveParameter] public string|null $updated_at,
        #[\SensitiveParameter] public SearchEnvelopeDataMediasItemCustomMetadataFieldValuesItemLastWrite|null $last_write,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['key', 'field_type', 'value', 'updated_at', 'last_write'], []);
        return new self(
            property_exists($value, 'key') ? Wire::string($value->key) : null,
            property_exists($value, 'field_type') ? Wire::oneOf(Wire::string($value->field_type), ['text', 'number', 'date', 'boolean', 'single_select', 'short_text', 'url', 'email', 'money', 'time', 'datetime', 'multi_select', 'contact_ref', 'contact_multi_ref']) : null,
            property_exists($value, 'value') ? MetadataValue::decode($value->value) : null,
            property_exists($value, 'updated_at') ? Wire::string($value->updated_at) : null,
            property_exists($value, 'last_write') ? ($value->last_write === null ? null : SearchEnvelopeDataMediasItemCustomMetadataFieldValuesItemLastWrite::fromWire($value->last_write)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
