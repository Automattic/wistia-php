<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class CreateChannelCollaboratorRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $channelHashedId,
        #[\SensitiveParameter] public string $email,
        #[\SensitiveParameter] public string $role,
    ) {
        Path::segment((string) $channelHashedId); Input::nonempty($channelHashedId);
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) { throw new \InvalidArgumentException('A valid collaborator email is required.'); }
        Input::oneOf($role, ['admin', 'viewer']);
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
         { $body->email = $this->email; }
         { $body->role = $this->role; }
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
