<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Stats;

use Automattic\Wistia\Encoding\Query;
use Automattic\Wistia\Media\Input;
use Automattic\Wistia\Stats\Validation;

final readonly class ShowVisitorRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $visitorKey,
    ) {
        Input::nonempty($visitorKey);
    }
    public function query(): Query
    {
        return new Query();
    }
    public function __debugInfo(): array { return []; }
}
