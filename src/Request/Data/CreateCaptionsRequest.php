<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class CreateCaptionsRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $mediaHashedId,
        #[\SensitiveParameter] public string $caption_file,
        #[\SensitiveParameter] public ?string $language = null,
    ) {
        Path::segment($mediaHashedId); Input::nonempty($mediaHashedId);
        Input::nonempty($caption_file);
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
        $body->caption_file = $this->caption_file;
        if ($this->language !== null) { $body->language = $this->language; }
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
