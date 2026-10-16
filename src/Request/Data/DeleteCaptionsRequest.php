<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class DeleteCaptionsRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $mediaHashedId,
        #[\SensitiveParameter] public string $languageCode,
    ) {
        Path::segment($mediaHashedId); Input::nonempty($mediaHashedId);
        Path::segment($languageCode); if (preg_match('~^[a-z]{3}$~D', $languageCode) !== 1) { throw new \InvalidArgumentException('Invalid language code.'); } Input::nonempty($languageCode);
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
