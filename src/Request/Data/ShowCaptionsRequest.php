<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class ShowCaptionsRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $mediaHashedId,
        #[\SensitiveParameter] public string $languageCode,
        #[\SensitiveParameter] public ?string $include = null,
        #[\SensitiveParameter] public ?bool $include_speakers = null,
        public CaptionFormat $format = CaptionFormat::Json,
    ) {
        Path::segment($mediaHashedId); Input::nonempty($mediaHashedId);
        Path::segment($languageCode); Input::nonempty($languageCode);
        if ($include !== null) { Input::oneOf($include, ['segments', 'diarized_segments']); }
    }
    private function pairs(): array
    {
        $pairs = [];
        if ($this->include !== null) { $pairs[] = ['include', $this->include]; }
        if ($this->include_speakers !== null) { $pairs[] = ['include_speakers', $this->include_speakers]; }
        return $pairs;
    }
    public function query(): Query { return new Query($this->pairs()); }
    public function body(): ?Body
    {
        return null;
    }
    public function __debugInfo(): array { return []; }
}
