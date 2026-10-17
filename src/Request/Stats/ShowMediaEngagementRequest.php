<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Stats;

use Automattic\Wistia\Encoding\Query;
use Automattic\Wistia\Media\Input;
use Automattic\Wistia\Stats\Validation;

final readonly class ShowMediaEngagementRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $mediaId,
    ) {
        Input::nonempty($mediaId);
    }
    public function query(): Query
    {
        return new Query();
    }
    public function __debugInfo(): array { return []; }
}
