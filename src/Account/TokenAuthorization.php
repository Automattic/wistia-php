<?php
declare(strict_types=1);

namespace Automattic\Wistia\Account;

use Automattic\Wistia\Media\Input;

/** One named resource rule; the server determines ownership and granted access. */
final readonly class TokenAuthorization implements \JsonSerializable
{
    /** @param list<string> $permissions */
    public function __construct(
        #[\SensitiveParameter] public string $type,
        #[\SensitiveParameter] public string $id,
        #[\SensitiveParameter] public array $permissions,
    ) {
        Input::oneOf($type, ['media', 'folder', 'account', 'webinar', 'channel', 'review-bundle', 'brand']);
        Input::nonempty($id);
        Input::strings($permissions);
    }
    public function jsonSerialize(): array
    {
        return ['type' => $this->type, 'id' => $this->id, 'permissions' => $this->permissions];
    }
    public function __debugInfo(): array { return []; }
}
