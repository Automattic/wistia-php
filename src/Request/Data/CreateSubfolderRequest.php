<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class CreateSubfolderRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $folderId,
        #[\SensitiveParameter] public string $name,
        #[\SensitiveParameter] public string|null|\Automattic\Wistia\Folders\Omitted $description = \Automattic\Wistia\Folders\Omitted::Value,
    ) {
        Path::segment((string) $folderId); Input::nonempty($folderId);
        \Automattic\Wistia\Folders\Input::length($name, 255);
        if ($description !== null && !$description instanceof \Automattic\Wistia\Folders\Omitted) { \Automattic\Wistia\Folders\Input::length($description, 1000); }
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
        $body->name = $this->name;
        if (!$this->description instanceof \Automattic\Wistia\Folders\Omitted) { $body->description = $this->description; }
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
