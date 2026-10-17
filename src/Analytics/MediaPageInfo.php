<?php
declare(strict_types=1);

namespace Automattic\Wistia\Analytics;

use Automattic\Wistia\Media\Wire;

final readonly class MediaPageInfo
{
    public function __construct(
        #[\SensitiveParameter] public bool|null $has_next_page,
        #[\SensitiveParameter] public bool|null $has_previous_page,
        #[\SensitiveParameter] public string|null $end_cursor,
        #[\SensitiveParameter] public string|null $start_cursor,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['has_next_page','has_previous_page','end_cursor','start_cursor'], []);
        return new self(
            property_exists($value, 'has_next_page') ? Wire::boolean($value->has_next_page) : null,
            property_exists($value, 'has_previous_page') ? Wire::boolean($value->has_previous_page) : null,
            property_exists($value, 'end_cursor') && $value->end_cursor !== null ? Wire::string($value->end_cursor) : null,
            property_exists($value, 'start_cursor') && $value->start_cursor !== null ? Wire::string($value->start_cursor) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
