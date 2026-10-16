<?php
declare(strict_types=1);

namespace Automattic\Wistia\Media;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class ShowMediaAggregatedStatsData
{
    public function __construct(
        #[\SensitiveParameter] public ?int $id,
        #[\SensitiveParameter] public ?string $hashed_id,
        #[\SensitiveParameter] public ?string $name,
        #[\SensitiveParameter] public ?ShowMediaAggregatedStatsDataStats $stats,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['id', 'hashed_id', 'name', 'stats'], []);
        return new self(
            property_exists($value, 'id') ? Wire::integer($value->id) : null,
            property_exists($value, 'hashed_id') ? Wire::string($value->hashed_id) : null,
            property_exists($value, 'name') ? Wire::string($value->name) : null,
            property_exists($value, 'stats') ? ShowMediaAggregatedStatsDataStats::fromWire($value->stats) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
