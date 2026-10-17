<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Stats;

use Automattic\Wistia\Encoding\Query;
use Automattic\Wistia\Media\Input;
use Automattic\Wistia\Stats\Validation;

final readonly class ShowAccountStatsByDateRequest
{
    public function __construct(
        #[\SensitiveParameter] public ?string $start_date = null,
        #[\SensitiveParameter] public ?string $end_date = null,
    ) {
        if ($start_date !== null) { Validation::date($start_date); }
        if ($end_date !== null) { Validation::date($end_date); }
        Validation::range($start_date, $end_date);
    }
    public function query(): Query
    {
        $pairs = [];
        foreach (['start_date', 'end_date'] as $key) { if ($this->$key !== null) { $pairs[] = [$key, $this->$key]; } }
        return new Query($pairs);
    }
    public function __debugInfo(): array { return []; }
}
