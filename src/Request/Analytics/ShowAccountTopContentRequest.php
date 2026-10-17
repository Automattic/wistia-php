<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Analytics;

use Automattic\Wistia\Encoding\Query;
use Automattic\Wistia\Media\Input;
use Automattic\Wistia\Analytics\Validation;

final readonly class ShowAccountTopContentRequest
{
    /** @param list<string>|null $hashed_ids */
    public function __construct(
        #[\SensitiveParameter] public string $start_date,
        #[\SensitiveParameter] public string $end_date,
        #[\SensitiveParameter] public ?string $group_by = null,
        #[\SensitiveParameter] public ?array $hashed_ids = null,
        #[\SensitiveParameter] public ?string $sort_by = null,
        #[\SensitiveParameter] public ?string $sort_direction = null,
        #[\SensitiveParameter] public ?int $per_page = null,
    ) {
        Validation::range($start_date, $end_date);
        if ($group_by !== null) { Input::oneOf($group_by, ['media','channel','project']); }
        if ($hashed_ids !== null) {
            Input::strings($hashed_ids); Input::count($hashed_ids, 0, 1000);
            if ($group_by !== null && $group_by !== 'media') { throw new \InvalidArgumentException('hashed_ids requires group_by=media.'); }
        }
        if ($sort_by !== null) { Input::oneOf($sort_by, ['plays','loads','play_rate','engagement_rate','played_time','unique_visitors']); }
        if ($sort_direction !== null) { Input::oneOf($sort_direction, ['asc','desc']); }
        Validation::perPage($per_page, 1000);
    }
    public function query(): Query
    {
        $pairs = [];
        if ($this->start_date !== null) { $pairs[] = ['start_date', $this->start_date]; }
        if ($this->end_date !== null) { $pairs[] = ['end_date', $this->end_date]; }
        if ($this->group_by !== null) { $pairs[] = ['group_by', $this->group_by]; }
        if ($this->hashed_ids !== null) { foreach ($this->hashed_ids as $id) { $pairs[] = ['hashed_ids[]', $id]; } }
        if ($this->sort_by !== null) { $pairs[] = ['sort_by', $this->sort_by]; }
        if ($this->sort_direction !== null) { $pairs[] = ['sort_direction', $this->sort_direction]; }
        if ($this->per_page !== null) { $pairs[] = ['per_page', $this->per_page]; }
        return new Query($pairs);
    }
    public function __debugInfo(): array { return []; }
}
