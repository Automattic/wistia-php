<?php
declare(strict_types=1);

namespace Automattic\Wistia\Media;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class ShowMediaAggregatedStatsDataStats
{
    public function __construct(
        #[\SensitiveParameter] public ?int $pageLoads,
        #[\SensitiveParameter] public ?int $visitors,
        #[\SensitiveParameter] public ?int $percentOfVisitorsClickingPlay,
        #[\SensitiveParameter] public ?int $plays,
        #[\SensitiveParameter] public ?int $averagePercentWatched,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['pageLoads', 'visitors', 'percentOfVisitorsClickingPlay', 'plays', 'averagePercentWatched'], []);
        return new self(
            property_exists($value, 'pageLoads') ? Wire::integer($value->pageLoads) : null,
            property_exists($value, 'visitors') ? Wire::integer($value->visitors) : null,
            property_exists($value, 'percentOfVisitorsClickingPlay') ? Wire::integer($value->percentOfVisitorsClickingPlay) : null,
            property_exists($value, 'plays') ? Wire::integer($value->plays) : null,
            property_exists($value, 'averagePercentWatched') ? Wire::integer($value->averagePercentWatched) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
