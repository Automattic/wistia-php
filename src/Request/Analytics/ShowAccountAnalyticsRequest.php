<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Analytics;

use Automattic\Wistia\Encoding\Query;
use Automattic\Wistia\Analytics\Validation;

final readonly class ShowAccountAnalyticsRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $start_date,
        #[\SensitiveParameter] public string $end_date,
    ) {
        Validation::range($start_date, $end_date);
    }
    public function query(): Query
    {
        $pairs = [];
        if ($this->start_date !== null) { $pairs[] = ['start_date', $this->start_date]; }
        if ($this->end_date !== null) { $pairs[] = ['end_date', $this->end_date]; }
        return new Query($pairs);
    }
    public function __debugInfo(): array { return []; }
}
