<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class CreateTagsRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $name,
    ) {
        Input::nonempty($name);
        Input::tag($name);
    }
    public function query(): Query
    {
        $pairs = [];

        return new Query($pairs);
    }
    public function body(): ?Body
    {
        $body = new \stdClass();
        $body->name = $this->name;
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
