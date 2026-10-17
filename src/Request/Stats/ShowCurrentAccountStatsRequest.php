<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Stats;

use Automattic\Wistia\Encoding\Query;
use Automattic\Wistia\Media\Input;
use Automattic\Wistia\Stats\Validation;

final readonly class ShowCurrentAccountStatsRequest
{
    public function __construct(

    ) {

    }
    public function query(): Query
    {
        return new Query();
    }
    public function __debugInfo(): array { return []; }
}
