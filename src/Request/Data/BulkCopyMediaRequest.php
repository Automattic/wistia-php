<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class BulkCopyMediaRequest
{
    public function __construct(
        #[\SensitiveParameter] public array $hashed_ids,
        #[\SensitiveParameter] public string $folder_id,
    ) {
        Input::strings($hashed_ids);
        Input::nonempty($folder_id);
        Input::count($hashed_ids, 1);
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
        $body->folder_id = $this->folder_id;
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
