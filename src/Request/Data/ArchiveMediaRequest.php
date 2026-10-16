<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class ArchiveMediaRequest
{
    public function __construct(
        #[\SensitiveParameter] public array $hashed_ids,
    ) {
        Input::strings($hashed_ids);
        Input::count($hashed_ids, 1, 100);
    }
    public function query(): Query
    {
        $pairs = [];

        return new Query($pairs);
    }
    public function body(): ?Body
    {
        $body = new \stdClass();
        $body->hashed_ids = $this->hashed_ids;
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
