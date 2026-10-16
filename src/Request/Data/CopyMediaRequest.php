<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class CopyMediaRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $mediaHashedId,
        #[\SensitiveParameter] public ?int $folder_id = null,
        #[\SensitiveParameter] public ?string $owner = null,
    ) {
        Path::segment($mediaHashedId); Input::nonempty($mediaHashedId);
    }
    public function query(): Query
    {
        $pairs = [];

        return new Query($pairs);
    }
    public function body(): ?Body
    {
        $body = new \stdClass();
        if ($this->folder_id !== null) { $body->folder_id = $this->folder_id; }
        if ($this->owner !== null) { $body->owner = $this->owner; }
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
