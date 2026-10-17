<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class UpdateShareLinkRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $mediaId,
        #[\SensitiveParameter] public string $visibility,
    ) {
        Input::nonempty($mediaId);
        Input::nonempty($visibility);
        Input::oneOf($visibility, ['unlocked', 'account', 'locked', 'domain_verified']);
    }
    public function query(): Query { return new Query([]); }
    public function body(): ?Body { $data = []; $data['visibility'] = $this->visibility; return Body::json($data); }
    public function __debugInfo(): array { return []; }
}
