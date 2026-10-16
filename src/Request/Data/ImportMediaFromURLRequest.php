<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class ImportMediaFromURLRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $url,
        #[\SensitiveParameter] public ?string $folder_id = null,
    ) {
        Input::nonempty($url);
        Input::url($url);
    }
    public function query(): Query
    {
        $pairs = [];

        return new Query($pairs);
    }
    public function body(): ?Body
    {
        $body = new \stdClass();
        $body->url = $this->url;
        if ($this->folder_id !== null) { $body->folder_id = $this->folder_id; }
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
