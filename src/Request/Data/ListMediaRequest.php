<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class ListMediaRequest
{
    public function __construct(
        #[\SensitiveParameter] public ?int $page = null,
        #[\SensitiveParameter] public ?int $per_page = null,
        #[\SensitiveParameter] public ?Cursor $cursor = null,
        #[\SensitiveParameter] public ?string $sort_by = null,
        #[\SensitiveParameter] public ?int $sort_direction = null,
        #[\SensitiveParameter] public ?string $folder_id = null,
        #[\SensitiveParameter] public ?string $name = null,
        #[\SensitiveParameter] public ?string $description_format = null,
        #[\SensitiveParameter] public ?string $include = null,
        #[\SensitiveParameter] public ?string $type = null,
        #[\SensitiveParameter] public ?array $hashed_ids = null,
        #[\SensitiveParameter] public ?array $tags = null,
        #[\SensitiveParameter] public ?bool $archived = null,
    ) {
        if ($page !== null) { if ($page < 1) { throw new \InvalidArgumentException('Pagination values must be positive.'); } }
        if ($per_page !== null) { if ($per_page < 1) { throw new \InvalidArgumentException('Pagination values must be positive.'); } }
        if ($sort_direction !== null) { Input::oneOf($sort_direction, [0, 1]); }
        if ($description_format !== null) { Input::oneOf($description_format, ['markdown']); }
        if ($include !== null) { Input::oneOf($include, ['speakers']); }
        if ($type !== null) { Input::oneOf($type, ['Video', 'Audio', 'Image', 'PdfDocument', 'MicrosoftOfficeDocument', 'Swf', 'UnknownType']); }
        if ($hashed_ids !== null) { Input::strings($hashed_ids); }
        if ($tags !== null) { Input::strings($tags); }
        if ($page !== null && $cursor !== null) { throw new \InvalidArgumentException('Do not mix page and cursor pagination.'); }
        if ($cursor?->active() && $sort_by !== null) { Input::oneOf($sort_by, ['id', 'created']); }
        if (!$cursor?->active() && $sort_by !== null) { Input::oneOf($sort_by, ['name', 'created', 'updated', 'position']); }
    }
    private function pairs(): array
    {
        $pairs = [];
        if ($this->page !== null) { $pairs[] = ['page', $this->page]; }
        if ($this->per_page !== null) { $pairs[] = ['per_page', $this->per_page]; }
        if ($this->cursor !== null) { $pairs = [...$pairs, ...$this->cursor->pairs()]; }
        if ($this->sort_by !== null) { $pairs[] = ['sort_by', $this->sort_by]; }
        if ($this->sort_direction !== null) { $pairs[] = ['sort_direction', $this->sort_direction]; }
        if ($this->folder_id !== null) { $pairs[] = ['folder_id', $this->folder_id]; }
        if ($this->name !== null) { $pairs[] = ['name', $this->name]; }
        if ($this->description_format !== null) { $pairs[] = ['description_format', $this->description_format]; }
        if ($this->include !== null) { $pairs[] = ['include', $this->include]; }
        if ($this->type !== null) { $pairs[] = ['type', $this->type]; }
        foreach ($this->hashed_ids ?? [] as $value) { $pairs[] = ['hashed_ids[]', $value]; }
        foreach ($this->tags ?? [] as $value) { $pairs[] = ['tags[]', $value]; }
        if ($this->archived !== null) { $pairs[] = ['archived', $this->archived]; }
        return $pairs;
    }
    public function query(): Query { return new Query($this->pairs()); }

    public function paginationStrategy(): \Automattic\Wistia\Pagination\PaginationStrategy
    {
        $filters = array_values(array_filter($this->pairs(), static fn($pair) =>
            !in_array($pair[0], ['page', 'per_page', 'cursor[enabled]', 'cursor[before]', 'cursor[after]'], true)));
        if ($this->cursor?->active()) {
            return new \Automattic\Wistia\Pagination\DataCursorPagination($filters, $this->per_page ?? 100,
                $this->cursor->before ?? $this->cursor->after, $this->cursor->before !== null);
        }
        return new \Automattic\Wistia\Pagination\OffsetPagination($filters, $this->per_page ?? 100, $this->page ?? 1);
    }
    public function body(): ?Body
    {
        return null;
    }
    public function __debugInfo(): array { return []; }
}
