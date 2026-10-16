<?php
declare(strict_types=1);

namespace Automattic\Wistia\Pagination;

use Automattic\Wistia\Encoding\Query;

final readonly class OffsetPagination implements PaginationStrategy
{
    public function __construct(#[\SensitiveParameter] private array $filters = [], private int $perPage = 100, private int $startPage = 1)
    {
        Parameters::validate($filters); Parameters::size($perPage);
        if ($startPage < 1) { throw new \InvalidArgumentException('Page numbers start at one.'); }
    }
    public function first(): int { return $this->startPage; }
    public function query(#[\SensitiveParameter] int|string|null $position): Query
    {
        if (!is_int($position) || $position < 1) { throw new \InvalidArgumentException('Page numbers must be positive integers.'); }
        return new Query([...$this->filters, ['per_page', $this->perPage], ['page', $position]]);
    }
    public function next(#[\SensitiveParameter] Page $page, #[\SensitiveParameter] int|string|null $position): ?int
    {
        if (count($page->items) < $this->perPage) { return null; }
        if (!is_int($position) || $position === PHP_INT_MAX) { throw new \OverflowException('Page number cannot advance.'); }
        return $position + 1;
    }
    public function __debugInfo(): array { return []; }
}
