<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class BulkDeleteSubfoldersRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $folderId,
        /** @var list<string>|null */ #[\SensitiveParameter] public array $hashed_ids,
    ) {
        Path::segment((string) $folderId); Input::nonempty($folderId);
        Input::strings($hashed_ids);
    }
    private function pairs(): array
    {
        $pairs = [];

        return $pairs;
    }
    public function query(): Query { return new Query($this->pairs()); }
    public function body(): ?Body
    {
        $body = new \stdClass();
        $body->hashed_ids = $this->hashed_ids;
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
