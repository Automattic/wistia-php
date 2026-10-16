<?php
declare(strict_types=1);

namespace Automattic\Wistia\Pagination;

use Automattic\Wistia\Encoding\Query;

final readonly class AnalyticsCursorPagination implements PaginationStrategy
{
    public function __construct(#[\SensitiveParameter] private array $filters = [], private int $perPage = 25,
        #[\SensitiveParameter] private ?string $cursor = null)
    {
        Parameters::validate($filters); Parameters::size($perPage); Parameters::cursor($cursor);
    }
    public function first(): ?string { return $this->cursor; }
    public function query(#[\SensitiveParameter] int|string|null $position): Query
    {
        Parameters::cursor($position);
        return new Query([...$this->filters, ['per_page', $this->perPage], ...($position === null ? [] : [['cursor', $position]])]);
    }
    public function next(#[\SensitiveParameter] Page $page, #[\SensitiveParameter] int|string|null $position): ?string
    {
        return $page->hasNextPage ? Parameters::advance($page->endCursor) : null;
    }
    public static function fromEnvelope(#[\SensitiveParameter] object $envelope): Page { return PageInfo::fromEnvelope($envelope, 'data'); }
    public function __debugInfo(): array { return []; }
}
