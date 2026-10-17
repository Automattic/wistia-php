<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Analytics;

use Automattic\Wistia\Encoding\Query;
use Automattic\Wistia\Analytics\Validation;
use Automattic\Wistia\Media\Input;

final readonly class ShowMediaTrafficBreakdownRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $mediaId,
        #[\SensitiveParameter] public string $start_date,
        #[\SensitiveParameter] public string $end_date,
        #[\SensitiveParameter] public string $group_by,
        #[\SensitiveParameter] public ?string $sort_by = null,
        #[\SensitiveParameter] public ?string $sort_direction = null,
        #[\SensitiveParameter] public ?int $per_page = null,
    ) {
        Input::nonempty($mediaId);
        Validation::range($start_date, $end_date);
        Input::oneOf($group_by, ['utm_campaign', 'utm_source', 'utm_medium', 'referrer_domain', 'viewer_screen_size']);
        if ($sort_by !== null) { Input::oneOf($sort_by, ['plays', 'loads', 'engagement_rate']); }
        if ($sort_direction !== null) { Input::oneOf($sort_direction, ['asc', 'desc']); }
        Validation::perPage($per_page, 100);
    }
    public function query(): Query
    {
        $pairs = [];
        $pairs[] = ['start_date', $this->start_date];
        $pairs[] = ['end_date', $this->end_date];
        $pairs[] = ['group_by', $this->group_by];
        if ($this->sort_by !== null) { $pairs[] = ['sort_by', $this->sort_by]; }
        if ($this->sort_direction !== null) { $pairs[] = ['sort_direction', $this->sort_direction]; }
        if ($this->per_page !== null) { $pairs[] = ['per_page', $this->per_page]; }
        return new Query($pairs);
    }
    public function __debugInfo(): array { return []; }
}
