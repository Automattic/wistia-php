<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Analytics;

use Automattic\Wistia\Encoding\Query;
use Automattic\Wistia\Analytics\Validation;
use Automattic\Wistia\Media\Input;

final readonly class ShowMediaFormConversionsRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $mediaId,
        #[\SensitiveParameter] public string $start_date,
        #[\SensitiveParameter] public string $end_date,
        #[\SensitiveParameter] public ?int $per_page = null,
        #[\SensitiveParameter] public ?string $cursor = null,
    ) {
        Input::nonempty($mediaId);
        Validation::range($start_date, $end_date);
        Validation::perPage($per_page, 100);
        \Automattic\Wistia\Pagination\Parameters::cursor($cursor);
    }
    public function query(): Query
    {
        $pairs = [];
        $pairs[] = ['start_date', $this->start_date];
        $pairs[] = ['end_date', $this->end_date];
        if ($this->per_page !== null) { $pairs[] = ['per_page', $this->per_page]; }
        if ($this->cursor !== null) { $pairs[] = ['cursor', $this->cursor]; }
        return new Query($pairs);
    }

    public function paginationStrategy(): \Automattic\Wistia\Pagination\AnalyticsCursorPagination
    {
        return new \Automattic\Wistia\Pagination\AnalyticsCursorPagination(
            [['start_date', $this->start_date], ['end_date', $this->end_date]],
            $this->per_page ?? 25, $this->cursor);
    }
    public function __debugInfo(): array { return []; }
}
