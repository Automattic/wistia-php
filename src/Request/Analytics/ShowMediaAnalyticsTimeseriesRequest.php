<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Analytics;

use Automattic\Wistia\Encoding\Query;
use Automattic\Wistia\Analytics\Validation;
use Automattic\Wistia\Media\Input;

final readonly class ShowMediaAnalyticsTimeseriesRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $mediaId,
        #[\SensitiveParameter] public string $start_date,
        #[\SensitiveParameter] public string $end_date,
        #[\SensitiveParameter] public string $granularity,
    ) {
        Input::nonempty($mediaId);
        Validation::range($start_date, $end_date);
        Input::oneOf($granularity, ['daily', 'weekly', 'monthly']);
    }
    public function query(): Query
    {
        $pairs = [];
        $pairs[] = ['start_date', $this->start_date];
        $pairs[] = ['end_date', $this->end_date];
        $pairs[] = ['granularity', $this->granularity];
        return new Query($pairs);
    }
    public function __debugInfo(): array { return []; }
}
