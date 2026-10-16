<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class InviteContactsRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $contacts,
    ) {
        Input::nonempty($contacts);
    }
    public function query(): Query { return new Query([]); }
    public function body(): ?Body { return Body::json(['contacts' => $this->contacts]); }
    public function __debugInfo(): array { return []; }
}
