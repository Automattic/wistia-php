<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Analytics;

use Automattic\Wistia\Encoding\Query;
use Automattic\Wistia\Analytics\Validation;
use Automattic\Wistia\Media\Input;

final readonly class ShowWebinarAnalyticsRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $webinarId,
        #[\SensitiveParameter] public ?bool $include_post_event = null,
        #[\SensitiveParameter] public ?string $post_event_start_date = null,
        #[\SensitiveParameter] public ?string $post_event_end_date = null,
    ) {
        Input::nonempty($webinarId);
        Validation::range($post_event_start_date, $post_event_end_date, false);
    }
    public function query(): Query
    {
        $pairs = [];
        if ($this->include_post_event !== null) { $pairs[] = ['include_post_event', $this->include_post_event]; }
        if ($this->post_event_start_date !== null) { $pairs[] = ['post_event_start_date', $this->post_event_start_date]; }
        if ($this->post_event_end_date !== null) { $pairs[] = ['post_event_end_date', $this->post_event_end_date]; }
        return new Query($pairs);
    }
    public function __debugInfo(): array { return []; }
}
