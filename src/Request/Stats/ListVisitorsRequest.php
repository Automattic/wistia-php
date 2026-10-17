<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Stats;

use Automattic\Wistia\Encoding\Query;
use Automattic\Wistia\Media\Input;
use Automattic\Wistia\Stats\Validation;

final readonly class ListVisitorsRequest
{
    public function __construct(
        #[\SensitiveParameter] public ?int $page = null,
        #[\SensitiveParameter] public ?int $per_page = null,
        #[\SensitiveParameter] public ?string $filter = null,
        #[\SensitiveParameter] public ?string $search = null,
    ) {
        if ($page !== null && ($page < 1)) { throw new \InvalidArgumentException('Pagination is outside the supported range.'); }
        if ($per_page !== null && ($per_page < 1 || $per_page > 100)) { throw new \InvalidArgumentException('Pagination is outside the supported range.'); }
        if ($filter !== null) { Input::oneOf($filter, ["has_name", "has_email", "identified_by_email_gate"]); }
    }
    public function query(): Query
    {
        $pairs = [];
        foreach (['page', 'per_page', 'filter', 'search'] as $key) { if ($this->$key !== null) { $pairs[] = [$key, $this->$key]; } }
        return new Query($pairs);
    }
    private function filters(): array
    {
        $pairs = [];
        foreach (['filter', 'search'] as $key) { if ($this->$key !== null) { $pairs[] = [$key, $this->$key]; } }
        return $pairs;
    }
    public function paginationStrategy(): \Automattic\Wistia\Pagination\OffsetPagination
    {
        return new \Automattic\Wistia\Pagination\OffsetPagination($this->filters(), $this->per_page ?? 100, $this->page ?? 1);
    }
    public function __debugInfo(): array { return []; }
}
