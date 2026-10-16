<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class ShowSubfolderRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $folderId,
        #[\SensitiveParameter] public string $subfolderId,
        #[\SensitiveParameter] public ?string $description_format = null,
    ) {
        Path::segment((string) $folderId); Input::nonempty($folderId);
        Path::segment((string) $subfolderId); Input::nonempty($subfolderId);
        if ($description_format !== null) { Input::oneOf($description_format, ['markdown']); }
    }
    private function pairs(): array
    {
        $pairs = [];
        if ($this->description_format !== null) { $pairs[] = ['description_format', $this->description_format]; }
        return $pairs;
    }
    public function query(): Query { return new Query($this->pairs()); }
    public function body(): ?Body
    {
        return null;
    }
    public function __debugInfo(): array { return []; }
}
