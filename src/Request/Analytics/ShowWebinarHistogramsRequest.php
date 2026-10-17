<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Analytics;

use Automattic\Wistia\Encoding\Query;
use Automattic\Wistia\Analytics\Validation;
use Automattic\Wistia\Media\Input;

final readonly class ShowWebinarHistogramsRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $webinarId,
    ) {
        Input::nonempty($webinarId);
    }
    public function query(): Query
    {
        $pairs = [];

        return new Query($pairs);
    }
    public function __debugInfo(): array { return []; }
}
