<?php
declare(strict_types=1);

namespace Automattic\Wistia\Accessibility;

use Automattic\Wistia\Media\Wire;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class ListSpeakersDataItem
{
    public function __construct(
        #[\SensitiveParameter] public string $speaker_profile_id,
        #[\SensitiveParameter] public string $name,
        #[\SensitiveParameter] public ?string $title,
        #[\SensitiveParameter] public ?string $image_url,
        #[\SensitiveParameter] public ?string $cursor,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['speaker_profile_id', 'name', 'title', 'image_url', 'cursor'], ['speaker_profile_id', 'name', 'title', 'image_url']);
        return new self(
            Wire::string($value->speaker_profile_id),
            Wire::string($value->name),
            ($value->title === null ? null : Wire::string($value->title)),
            ($value->image_url === null ? null : Wire::string($value->image_url)),
            property_exists($value, 'cursor') ? ($value->cursor === null ? null : Wire::string($value->cursor)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
