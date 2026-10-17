<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class ShowShareLinkRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $mediaId,
    ) {
        Input::nonempty($mediaId);
    }
    public function query(): Query { return new Query([]); }
    public function body(): ?Body { return null; }
    public function __debugInfo(): array { return []; }
}
