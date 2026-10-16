<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class ListWebinarRegistrationsRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $webinarId,
        #[\SensitiveParameter] public int|null $per_page = null,
        #[\SensitiveParameter] public string|null $cursor = null,
        #[\SensitiveParameter] public int|null $sort_direction = null,
        #[\SensitiveParameter] public string|null $attendance = null,
        #[\SensitiveParameter] public string|null $restriction = null,
        /** @var list<string>|null */
        #[\SensitiveParameter] public array|null $emails = null,
    ) {
        Path::segment((string) $webinarId); Input::nonempty($webinarId);
        \Automattic\Wistia\Pagination\Parameters::cursor($cursor);
        if ($per_page !== null) { if ($per_page < 1 || $per_page > 100) { throw new \InvalidArgumentException('Invalid pagination size or page.'); } }
        if ($sort_direction !== null) { Input::oneOf($sort_direction, [0, 1]); }
        if ($attendance !== null) { Input::oneOf($attendance, ['all', 'attendees', 'non_attendees']); }
        if ($restriction !== null) { Input::oneOf($restriction, ['all', 'restricted', 'allowed']); }
        if ($emails !== null) { Input::strings($emails); foreach ($emails as $email) { \Automattic\Wistia\Webinars\Validation::email($email); } }
    }
    private function pairs(): array
    {
        $pairs = [];
        if ($this->per_page !== null) { $pairs[] = ['per_page', $this->per_page]; }
        if ($this->cursor !== null) { $pairs[] = ['cursor', $this->cursor]; }
        if ($this->sort_direction !== null) { $pairs[] = ['sort_direction', $this->sort_direction]; }
        if ($this->attendance !== null) { $pairs[] = ['attendance', $this->attendance]; }
        if ($this->restriction !== null) { $pairs[] = ['restriction', $this->restriction]; }
        foreach ($this->emails ?? [] as $v) { $pairs[] = ['emails[]', $v]; }
        return $pairs;
    }
    public function query(): Query { return new Query($this->pairs()); }
    public function body(): ?Body
    {
        return null;
    }

    public function paginationStrategy(): \Automattic\Wistia\Pagination\DataCursorPagination
    {
        $filters = array_values(array_filter($this->pairs(), static fn($p) => !in_array($p[0], ['per_page', 'cursor', 'sort_direction'], true)));
        return new \Automattic\Wistia\Pagination\DataCursorPagination($filters, $this->per_page ?? 100, $this->cursor, $this->sort_direction === 0, pageInfo: true);
    }
    public function __debugInfo(): array { return []; }
}
