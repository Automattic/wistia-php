<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class CreateReviewBundleRequest
{
    public function __construct(
        #[\SensitiveParameter] public array $media_hashed_ids,
        #[\SensitiveParameter] public string $name,
        #[\SensitiveParameter] public bool|null $allow_downloads = null,
    ) {
        Input::strings($media_hashed_ids);
        Input::count($media_hashed_ids, 1, 25);
        Input::nonempty($name);
    }
    public function query(): Query { return new Query([]); }
    public function body(): ?Body { $data = []; $data['media_hashed_ids'] = $this->media_hashed_ids; $data['name'] = $this->name; if ($this->allow_downloads !== null) { $data['allow_downloads'] = $this->allow_downloads; } return Body::json($data); }
    public function __debugInfo(): array { return []; }
}
