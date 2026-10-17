<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Analytics;

use Automattic\Wistia\Encoding\Query;
use Automattic\Wistia\Media\Input;
use Automattic\Wistia\Analytics\Validation;

final readonly class ShowAccountAnalyticsTimeseriesRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $start_date,
        #[\SensitiveParameter] public string $end_date,
        #[\SensitiveParameter] public string $granularity,
    ) {
        Validation::range($start_date, $end_date);
        if ($granularity !== null) { Input::oneOf($granularity, ['daily','weekly','monthly']); }
    }
    public function query(): Query
    {
        $pairs = [];
        if ($this->start_date !== null) { $pairs[] = ['start_date', $this->start_date]; }
        if ($this->end_date !== null) { $pairs[] = ['end_date', $this->end_date]; }
        if ($this->granularity !== null) { $pairs[] = ['granularity', $this->granularity]; }
        return new Query($pairs);
    }
    public function __debugInfo(): array { return []; }
}
