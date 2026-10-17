<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class ResolveResourceURLsRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $type,
        #[\SensitiveParameter] public string $hashed_id,
    ) {
        Input::nonempty($type);
        Input::oneOf($type, ['media', 'folder', 'channel', 'channel_episode', 'webinar', 'remix']);
        Input::nonempty($hashed_id);
    }
    public function query(): Query { return new Query([['type', $this->type], ['hashed_id', $this->hashed_id]]); }
    public function body(): ?Body { return null; }
    public function __debugInfo(): array { return []; }
}
