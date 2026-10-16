<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class SwapMediaRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $mediaHashedId,
        #[\SensitiveParameter] public string $replacement_media_id,
    ) {
        Path::segment($mediaHashedId); Input::nonempty($mediaHashedId);
        Input::nonempty($replacement_media_id);
    }
    public function query(): Query
    {
        $pairs = [];

        return new Query($pairs);
    }
    public function body(): ?Body
    {
        $body = new \stdClass();
        $body->replacement_media_id = $this->replacement_media_id;
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
