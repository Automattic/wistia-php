<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class BulkTagMediaRequest
{
    public function __construct(
        #[\SensitiveParameter] public array $hashed_ids,
        #[\SensitiveParameter] public array $tag_names,
    ) {
        Input::strings($hashed_ids);
        Input::strings($tag_names);
        Input::count($hashed_ids, 1);
        Input::count($tag_names, 1);
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
        $body->tag_names = $this->tag_names;
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
