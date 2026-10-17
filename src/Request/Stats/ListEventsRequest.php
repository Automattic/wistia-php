<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Stats;

use Automattic\Wistia\Encoding\Query;
use Automattic\Wistia\Media\Input;
use Automattic\Wistia\Stats\Validation;

final readonly class ListEventsRequest
{
    public function __construct(
        #[\SensitiveParameter] public ?string $media_id = null,
        #[\SensitiveParameter] public ?string $visitor_key = null,
        #[\SensitiveParameter] public ?int $per_page = null,
        #[\SensitiveParameter] public ?int $page = null,
        #[\SensitiveParameter] public ?string $start_date = null,
        #[\SensitiveParameter] public ?string $end_date = null,
    ) {
        if ($per_page !== null && ($per_page < 1 || $per_page > 100)) { throw new \InvalidArgumentException('Pagination is outside the supported range.'); }
        if ($page !== null && ($page < 1)) { throw new \InvalidArgumentException('Pagination is outside the supported range.'); }
        if ($start_date !== null) { Validation::date($start_date); }
        if ($end_date !== null) { Validation::date($end_date); }
        Validation::range($start_date, $end_date);
    }
    public function query(): Query
    {
        $pairs = [];
        foreach (['media_id', 'visitor_key', 'per_page', 'page', 'start_date', 'end_date'] as $key) { if ($this->$key !== null) { $pairs[] = [$key, $this->$key]; } }
        return new Query($pairs);
    }
    private function filters(): array
    {
        $pairs = [];
        foreach (['media_id', 'visitor_key', 'start_date', 'end_date'] as $key) { if ($this->$key !== null) { $pairs[] = [$key, $this->$key]; } }
        return $pairs;
    }
    public function paginationStrategy(): \Automattic\Wistia\Pagination\OffsetPagination
    {
        return new \Automattic\Wistia\Pagination\OffsetPagination($this->filters(), $this->per_page ?? 100, $this->page ?? 1);
    }
    public function __debugInfo(): array { return []; }
}
