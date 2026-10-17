<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Query, Body};
use Automattic\Wistia\Media\Input;
use Automattic\Wistia\Pagination\{PaginationStrategy, DataCursorPagination, OffsetPagination};

final readonly class ListReviewBundlesRequest
{
    /** @param list<string>|null $hashed_ids */
    public function __construct(
        #[\SensitiveParameter] public ?array $hashed_ids = null,
        #[\SensitiveParameter] public ?string $name = null,
        #[\SensitiveParameter] public ?string $media_hashed_id = null,
        #[\SensitiveParameter] public ?string $folder_hashed_id = null,
        #[\SensitiveParameter] public ?string $sort_by = null,
        #[\SensitiveParameter] public ?int $sort_direction = null,
        #[\SensitiveParameter] public ?int $page = null,
        #[\SensitiveParameter] public ?int $per_page = null,
        #[\SensitiveParameter] public ?Cursor $cursor = null,
    ) {
        if ($hashed_ids !== null) { Input::strings($hashed_ids); }
        if ($sort_by !== null) { Input::oneOf($sort_by, ['id', 'name', 'created', 'updated']); }
        if ($sort_direction !== null) { Input::oneOf($sort_direction, [0, 1]); }
        if (($page !== null && $page < 1) || ($per_page !== null && $per_page < 1)) {
            throw new \InvalidArgumentException('Pagination values must be positive.');
        }
        if ($page !== null && $cursor !== null) { throw new \InvalidArgumentException('Do not mix page and cursor pagination.'); }
    }
    private function filters(): array
    {
        $pairs = [];
        foreach ($this->hashed_ids ?? [] as $id) { $pairs[] = ['hashed_ids[]', $id]; }
        foreach (['name', 'media_hashed_id', 'folder_hashed_id', 'sort_by', 'sort_direction'] as $key) {
            if ($this->$key !== null) { $pairs[] = [$key, $this->$key]; }
        }
        return $pairs;
    }
    public function query(): Query
    {
        $pairs = $this->filters();
        foreach (['page', 'per_page'] as $key) { if ($this->$key !== null) { $pairs[] = [$key, $this->$key]; } }
        return new Query([...$pairs, ...($this->cursor?->pairs() ?? [])]);
    }
    public function body(): ?Body { return null; }
    public function paginationStrategy(): PaginationStrategy
    {
        if ($this->cursor?->active()) {
            return new DataCursorPagination($this->filters(), $this->per_page ?? 100,
                $this->cursor->before ?? $this->cursor->after, $this->cursor->before !== null);
        }
        return new OffsetPagination($this->filters(), $this->per_page ?? 100, $this->page ?? 1);
    }
    public function __debugInfo(): array { return []; }
}
