<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class CreateFolderSharingRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $folderId,
        #[\SensitiveParameter] public \Automattic\Wistia\Folders\CreateSharing $sharing,
    ) {
        Path::segment((string) $folderId); Input::nonempty($folderId);
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
        $body->sharing = $this->sharing;
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
