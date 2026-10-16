<?php
declare(strict_types=1);

namespace Automattic\Wistia\Accessibility;

use Automattic\Wistia\Media\Wire;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class ListLocalizationsDataItem
{
    public function __construct(
        #[\SensitiveParameter] public string $hashed_id,
        #[\SensitiveParameter] public string $language_code,
        #[\SensitiveParameter] public string $iso639_2_language_code,
        #[\SensitiveParameter] public string $language_name,
        #[\SensitiveParameter] public string $native_language_name,
        #[\SensitiveParameter] public string $created_at,
        #[\SensitiveParameter] public string $updated_at,
        #[\SensitiveParameter] public ?string $ordered_at,
        #[\SensitiveParameter] public ?string $enabled_at,
        #[\SensitiveParameter] public bool $enabled,
        #[\SensitiveParameter] public ListLocalizationsDataItemSourceMedia $source_media,
        #[\SensitiveParameter] public ?ListLocalizationsDataItemTranscript $transcript,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['hashed_id', 'language_code', 'iso639_2_language_code', 'language_name', 'native_language_name', 'created_at', 'updated_at', 'ordered_at', 'enabled_at', 'enabled', 'source_media', 'transcript'], ['hashed_id', 'language_code', 'iso639_2_language_code', 'language_name', 'native_language_name', 'created_at', 'updated_at', 'ordered_at', 'enabled_at', 'enabled', 'source_media']);
        return new self(
            Wire::string($value->hashed_id),
            Wire::string($value->language_code),
            Wire::string($value->iso639_2_language_code),
            Wire::string($value->language_name),
            Wire::string($value->native_language_name),
            Wire::string($value->created_at),
            Wire::string($value->updated_at),
            ($value->ordered_at === null ? null : Wire::string($value->ordered_at)),
            ($value->enabled_at === null ? null : Wire::string($value->enabled_at)),
            Wire::boolean($value->enabled),
            ListLocalizationsDataItemSourceMedia::fromWire($value->source_media),
            property_exists($value, 'transcript') ? ($value->transcript === null ? null : ListLocalizationsDataItemTranscript::fromWire($value->transcript)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
