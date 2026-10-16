<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class UpdateCaptionsRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $mediaHashedId,
        #[\SensitiveParameter] public string $languageCode,
        #[\SensitiveParameter] public string $caption_file,
    ) {
        Path::segment($mediaHashedId); Input::nonempty($mediaHashedId);
        Path::segment($languageCode); if (preg_match('~^[a-z]{3}$~D', $languageCode) !== 1) { throw new \InvalidArgumentException('Invalid language code.'); } Input::nonempty($languageCode);
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
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
