<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class UpdateFolderSharingRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $folderId,
        #[\SensitiveParameter] public string $sharingId,
        #[\SensitiveParameter] public ?\Automattic\Wistia\Folders\UpdateSharing $sharing = null,
    ) {
        Path::segment((string) $folderId); Input::nonempty($folderId);
        Path::segment((string) $sharingId); Input::nonempty($sharingId);
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
        if ($this->sharing !== null) { $body->sharing = $this->sharing; }
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
