<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class CreateFolderRequest
{
    public function __construct(
        #[\SensitiveParameter] public ?string $name = null,
        #[\SensitiveParameter] public ?string $adminEmail = null,
        #[\SensitiveParameter] public ?string $description = null,
        #[\SensitiveParameter] public ?bool $anonymousCanUpload = null,
        #[\SensitiveParameter] public ?bool $anonymousCanDownload = null,
        #[\SensitiveParameter] public ?bool $public = null,
        #[\SensitiveParameter] public ?bool $personalLibrary = null,
    ) {

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
        if ($this->name !== null) { $body->name = $this->name; }
        if ($this->adminEmail !== null) { $body->adminEmail = $this->adminEmail; }
        if ($this->description !== null) { $body->description = $this->description; }
        if ($this->anonymousCanUpload !== null) { $body->anonymousCanUpload = $this->anonymousCanUpload; }
        if ($this->anonymousCanDownload !== null) { $body->anonymousCanDownload = $this->anonymousCanDownload; }
        if ($this->public !== null) { $body->public = $this->public; }
        if ($this->personalLibrary !== null) { $body->personalLibrary = $this->personalLibrary; }
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
