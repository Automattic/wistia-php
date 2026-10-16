<?php
declare(strict_types=1);

namespace Automattic\Wistia\Accessibility;

use Automattic\Wistia\Media\Wire;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class FindCaptionMatchesDataResultsItemTracksItem
{
    public function __construct(
        #[\SensitiveParameter] public string $id,
        #[\SensitiveParameter] public string $language,
        #[\SensitiveParameter] public ?string $english_name,
        #[\SensitiveParameter] public ?string $native_name,
        #[\SensitiveParameter] public ?int $version,
        #[\SensitiveParameter] public bool $is_draft,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['id', 'language', 'english_name', 'native_name', 'version', 'is_draft'], ['id', 'language', 'version', 'is_draft']);
        return new self(
            Wire::string($value->id),
            Wire::string($value->language),
            property_exists($value, 'english_name') ? ($value->english_name === null ? null : Wire::string($value->english_name)) : null,
            property_exists($value, 'native_name') ? ($value->native_name === null ? null : Wire::string($value->native_name)) : null,
            ($value->version === null ? null : Wire::integer($value->version)),
            Wire::boolean($value->is_draft),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
