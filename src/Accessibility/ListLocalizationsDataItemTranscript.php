<?php
declare(strict_types=1);

namespace Automattic\Wistia\Accessibility;

use Automattic\Wistia\Media\Wire;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class ListLocalizationsDataItemTranscript
{
    public function __construct(
        #[\SensitiveParameter] public string $hashed_id,
        #[\SensitiveParameter] public string $language_name,
        #[\SensitiveParameter] public string $native_language_name,
        #[\SensitiveParameter] public string $text,
        #[\SensitiveParameter] public string $created_at,
        #[\SensitiveParameter] public string $updated_at,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['hashed_id', 'language_name', 'native_language_name', 'text', 'created_at', 'updated_at'], ['hashed_id', 'language_name', 'native_language_name', 'text', 'created_at', 'updated_at']);
        return new self(
            Wire::string($value->hashed_id),
            Wire::string($value->language_name),
            Wire::string($value->native_language_name),
            Wire::string($value->text),
            Wire::string($value->created_at),
            Wire::string($value->updated_at),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
