<?php
declare(strict_types=1);

namespace Automattic\Wistia\Pagination;

use Automattic\Wistia\Encoding\Query;

/** Nested record cursors by default; webinar registrations use the page_info variant. */
final readonly class DataCursorPagination implements PaginationStrategy
{
    public function __construct(#[\SensitiveParameter] private array $filters = [], private int $perPage = 100,
        #[\SensitiveParameter] private ?string $cursor = null, private bool $backwards = false, private bool $pageInfo = false)
    {
        Parameters::validate($filters); Parameters::size($perPage); Parameters::cursor($cursor);
        foreach ($filters as [$name, $value]) {
            if ($pageInfo && $name === 'sort_direction') { throw new \InvalidArgumentException('The webinar pagination adapter owns sort_direction.'); }
        }
    }
    public function first(): ?string { return $this->cursor; }
    public function query(#[\SensitiveParameter] int|string|null $position): Query
    {
        Parameters::cursor($position);
        $pairs = [...$this->filters, ['per_page', $this->perPage]];
        if ($this->pageInfo) {
            $pairs[] = ['sort_direction', $this->backwards ? 0 : 1];
            if ($position !== null) { $pairs[] = ['cursor', $position]; }
        } elseif ($position === null) { $pairs[] = ['cursor[enabled]', 1]; }
        else { $pairs[] = [$this->backwards ? 'cursor[before]' : 'cursor[after]', $position]; }
        return new Query($pairs);
    }
    public function next(#[\SensitiveParameter] Page $page, #[\SensitiveParameter] int|string|null $position): ?string
    {
        if ($this->pageInfo) {
            if (!($this->backwards ? $page->hasPreviousPage : $page->hasNextPage)) { return null; }
        } elseif (count($page->items) < $this->perPage) { return null; }
        return Parameters::advance($this->backwards ? $page->startCursor : $page->endCursor);
    }
    /** @param list<object> $records Raw decoded records, before an endpoint maps them to DTOs. */
    public static function fromRecords(#[\SensitiveParameter] array $records): Page
    {
        if (!array_is_list($records)) { throw new \UnexpectedValueException('Expected a record list.'); }
        foreach ($records as $record) {
            if (!is_object($record) || (property_exists($record, 'cursor') && !($record->cursor === null || is_string($record->cursor)))) {
                throw new \UnexpectedValueException('Record cursors must be strings or null when present.');
            }
        }
        return new Page($records, $records === [] ? null : ($records[array_key_last($records)]->cursor ?? null),
            false, $records === [] ? null : ($records[0]->cursor ?? null));
    }
    public static function fromWebinarRegistrations(#[\SensitiveParameter] object $envelope): Page
    {
        return PageInfo::fromEnvelope($envelope, 'registrations');
    }
    public function __debugInfo(): array { return []; }
}
