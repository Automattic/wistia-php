<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class UpdateMediaRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $mediaHashedId,
        #[\SensitiveParameter] public ?string $name = null,
        #[\SensitiveParameter] public ?string $new_still_media_id = null,
        #[\SensitiveParameter] public ?string $description = null,
        #[\SensitiveParameter] public ?array $tags = null,
        #[\SensitiveParameter] public ?\stdClass $custom_metadata = null,
    ) {
        Path::segment($mediaHashedId); Input::nonempty($mediaHashedId);
        if ($tags !== null) { Input::strings($tags); }
    }
    public function query(): Query
    {
        $pairs = [];

        return new Query($pairs);
    }
    public function body(): ?Body
    {
        $body = new \stdClass();
        if ($this->name !== null) { $body->name = $this->name; }
        if ($this->new_still_media_id !== null) { $body->new_still_media_id = $this->new_still_media_id; }
        if ($this->description !== null) { $body->description = $this->description; }
        if ($this->tags !== null) { $body->tags = $this->tags; }
        if ($this->custom_metadata !== null) { $body->custom_metadata = $this->custom_metadata; }
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
