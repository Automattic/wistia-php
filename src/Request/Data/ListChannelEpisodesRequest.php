<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class ListChannelEpisodesRequest
{
    public function __construct(
        #[\SensitiveParameter] public string|null $channel_id = null,
        #[\SensitiveParameter] public string|null $sort_by = null,
        #[\SensitiveParameter] public int|null $sort_direction = null,
        #[\SensitiveParameter] public int|null $page = null,
        #[\SensitiveParameter] public int|null $per_page = null,
        #[\SensitiveParameter] public Cursor|null $cursor = null,
        /** @var list<string>|null */
        #[\SensitiveParameter] public array|null $media_id = null,
        /** @var list<string>|null */
        #[\SensitiveParameter] public array|null $hashed_ids = null,
        #[\SensitiveParameter] public bool|null $published = null,
        #[\SensitiveParameter] public string|null $title = null,
    ) {
        if ($sort_by !== null) { Input::oneOf($sort_by, ['position', 'title', 'created', 'updated', 'published_at', 'id']); }
        if ($sort_direction !== null) { Input::oneOf($sort_direction, [0, 1]); }
        if ($page !== null) { if ($page < 1) { throw new \InvalidArgumentException('Pagination values must be positive.'); } }
        if ($per_page !== null) { if ($per_page < 1) { throw new \InvalidArgumentException('Pagination values must be positive.'); } }
        if ($media_id !== null) { Input::strings($media_id); }
        if ($hashed_ids !== null) { Input::strings($hashed_ids); }
        if ($page !== null && $cursor !== null) { throw new \InvalidArgumentException('Do not mix page and cursor pagination.'); }
        if ($cursor?->active() && $sort_by !== null) { Input::oneOf($sort_by, ['id', 'created']); }
    }
    private function pairs(): array
    {
        $pairs = [];
        if ($this->channel_id !== null) { $pairs[] = ['channel_id', $this->channel_id]; }
        if ($this->sort_by !== null) { $pairs[] = ['sort_by', $this->sort_by]; }
        if ($this->sort_direction !== null) { $pairs[] = ['sort_direction', $this->sort_direction]; }
        if ($this->page !== null) { $pairs[] = ['page', $this->page]; }
        if ($this->per_page !== null) { $pairs[] = ['per_page', $this->per_page]; }
        if ($this->cursor !== null) { $pairs = [...$pairs, ...$this->cursor->pairs()]; }
        foreach ($this->media_id ?? [] as $v) { $pairs[] = ['media_id[]', $v]; }
        foreach ($this->hashed_ids ?? [] as $v) { $pairs[] = ['hashed_ids[]', $v]; }
        if ($this->published !== null) { $pairs[] = ['published', $this->published]; }
        if ($this->title !== null) { $pairs[] = ['title', $this->title]; }
        return $pairs;
    }
    public function query(): Query { return new Query($this->pairs()); }
    public function body(): ?Body
    {
        return null;
    }

    public function paginationStrategy(): \Automattic\Wistia\Pagination\PaginationStrategy
    {
        $filters = array_values(array_filter($this->pairs(), static fn($p) =>
            !in_array($p[0], ['page', 'per_page', 'cursor[enabled]', 'cursor[before]', 'cursor[after]'], true)));
        if ($this->cursor?->active()) {
            return new \Automattic\Wistia\Pagination\DataCursorPagination($filters, $this->per_page ?? 100,
                $this->cursor->before ?? $this->cursor->after, $this->cursor->before !== null);
        }
        return new \Automattic\Wistia\Pagination\OffsetPagination($filters, $this->per_page ?? 100, $this->page ?? 1);
    }
    public function __debugInfo(): array { return []; }
}
