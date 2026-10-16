<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class ShowLocalizationRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $mediaHashedId,
        #[\SensitiveParameter] public string $localizationHashedId,
        #[\SensitiveParameter] public ?bool $include_transcript = null,
    ) {
        Path::segment($mediaHashedId); Input::nonempty($mediaHashedId);
        Path::segment($localizationHashedId); Input::nonempty($localizationHashedId);
    }
    private function pairs(): array
    {
        $pairs = [];
        if ($this->include_transcript !== null) { $pairs[] = ['include_transcript', $this->include_transcript]; }
        return $pairs;
    }
    public function query(): Query { return new Query($this->pairs()); }
    public function body(): ?Body
    {
        return null;
    }
    public function __debugInfo(): array { return []; }
}
