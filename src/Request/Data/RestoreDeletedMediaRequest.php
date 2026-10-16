<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class RestoreDeletedMediaRequest
{
    public function __construct(
        #[\SensitiveParameter] public array $media_hashed_ids,
        #[\SensitiveParameter] public ?string $folder_id = null,
    ) {
        Input::strings($media_hashed_ids);
        Input::count($media_hashed_ids, 1, 1000);
    }
    public function query(): Query
    {
        $pairs = [];

        return new Query($pairs);
    }
    public function body(): ?Body
    {
        $body = new \stdClass();
        $body->media_hashed_ids = $this->media_hashed_ids;
        if ($this->folder_id !== null) { $body->folder_id = $this->folder_id; }
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
