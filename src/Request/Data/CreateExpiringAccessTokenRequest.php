<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Query, Body};
use Automattic\Wistia\Media\Input;

/** Experimental: upstream may change this contract at any time. */
final readonly class CreateExpiringAccessTokenRequest
{
    public function __construct(
        #[\SensitiveParameter] public ?\Automattic\Wistia\Account\ExpiringTokenOptions $expiring_access_token = null,
    ) {
    }
    public function query(): Query { return new Query([]); }
    public function body(): ?Body { return Body::json($this->expiring_access_token === null ? new \stdClass() : ['expiring_access_token' => $this->expiring_access_token]); }
    public function __debugInfo(): array { return []; }
}
