<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class ShowMediaAggregatedStatsRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $mediaHashedId,
    ) {
        Path::segment($mediaHashedId); Input::nonempty($mediaHashedId);
    }
    public function query(): Query
    {
        $pairs = [];

        return new Query($pairs);
    }
    public function body(): ?Body
    {
        return null;
    }
    public function __debugInfo(): array { return []; }
}
