<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Analytics;

use Automattic\Wistia\Encoding\Query;
use Automattic\Wistia\Analytics\Validation;
use Automattic\Wistia\Media\Input;

final readonly class ShowWebinarTrafficBreakdownRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $webinarId,
        #[\SensitiveParameter] public string $group_by,
        #[\SensitiveParameter] public ?string $sort_by = null,
        #[\SensitiveParameter] public ?string $sort_direction = null,
    ) {
        Input::nonempty($webinarId);
        Input::oneOf($group_by, ['utm_campaign','utm_source','utm_medium','referrer_domain']);
        if ($sort_by !== null) { Input::oneOf($sort_by, ['registrations','attendees','impressions']); }
        if ($sort_direction !== null) { Input::oneOf($sort_direction, ['asc','desc']); }
    }
    public function query(): Query
    {
        $pairs = [];
        $pairs[] = ['group_by', $this->group_by];
        if ($this->sort_by !== null) { $pairs[] = ['sort_by', $this->sort_by]; }
        if ($this->sort_direction !== null) { $pairs[] = ['sort_direction', $this->sort_direction]; }
        return new Query($pairs);
    }
    public function __debugInfo(): array { return []; }
}
