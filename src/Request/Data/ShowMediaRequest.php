<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class ShowMediaRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $mediaHashedId,
        #[\SensitiveParameter] public ?string $description_format = null,
        #[\SensitiveParameter] public ?string $include = null,
    ) {
        Path::segment($mediaHashedId); Input::nonempty($mediaHashedId);
        if ($description_format !== null) { Input::oneOf($description_format, ['markdown']); }
        if ($include !== null) { Input::oneOf($include, ['speakers']); }
    }
    public function query(): Query
    {
        $pairs = [];
        if ($this->description_format !== null) { $pairs[] = ['description_format', $this->description_format]; }
        if ($this->include !== null) { $pairs[] = ['include', $this->include]; }
        return new Query($pairs);
    }
    public function body(): ?Body
    {
        return null;
    }
    public function __debugInfo(): array { return []; }
}
