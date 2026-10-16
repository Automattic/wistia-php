<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class MoveMediaRequest
{
    public function __construct(
        #[\SensitiveParameter] public array $hashed_ids,
        #[\SensitiveParameter] public string $folder_id,
        #[\SensitiveParameter] public ?string $subfolder_id = null,
    ) {
        Input::strings($hashed_ids);
        Input::nonempty($folder_id);
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
        $body->folder_id = $this->folder_id;
        if ($this->subfolder_id !== null) { $body->subfolder_id = $this->subfolder_id; }
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
