<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Analytics;

use Automattic\Wistia\Encoding\Query;
use Automattic\Wistia\Media\Input;
use Automattic\Wistia\Analytics\Validation;

final readonly class ShowAccountEmbedLocationsRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $start_date,
        #[\SensitiveParameter] public string $end_date,
        #[\SensitiveParameter] public ?string $sort_by = null,
        #[\SensitiveParameter] public ?string $sort_direction = null,
        #[\SensitiveParameter] public ?int $per_page = null,
    ) {
        Validation::range($start_date, $end_date);
        if ($sort_by !== null) { Input::oneOf($sort_by, ['plays','loads','engagement_rate','play_rate','played_time','unique_visitors']); }
        if ($sort_direction !== null) { Input::oneOf($sort_direction, ['asc','desc']); }
        Validation::perPage($per_page, 100);
    }
    public function query(): Query
    {
        $pairs = [];
        if ($this->start_date !== null) { $pairs[] = ['start_date', $this->start_date]; }
        if ($this->end_date !== null) { $pairs[] = ['end_date', $this->end_date]; }
        if ($this->sort_by !== null) { $pairs[] = ['sort_by', $this->sort_by]; }
        if ($this->sort_direction !== null) { $pairs[] = ['sort_direction', $this->sort_direction]; }
        if ($this->per_page !== null) { $pairs[] = ['per_page', $this->per_page]; }
        return new Query($pairs);
    }
    public function __debugInfo(): array { return []; }
}
