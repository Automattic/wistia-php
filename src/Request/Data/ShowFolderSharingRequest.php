<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class ShowFolderSharingRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $folderId,
        #[\SensitiveParameter] public int $sharingId,
    ) {
        Path::segment((string) $folderId); Input::nonempty($folderId);
        Path::segment((string) $sharingId);
    }
    private function pairs(): array
    {
        $pairs = [];

        return $pairs;
    }
    public function query(): Query { return new Query($this->pairs()); }
    public function body(): ?Body
    {
        return null;
    }
    public function __debugInfo(): array { return []; }
}
