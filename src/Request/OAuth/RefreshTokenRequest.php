<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\OAuth;

final readonly class RefreshTokenRequest implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public string $clientId,
        #[\SensitiveParameter] public string $clientSecret,
        #[\SensitiveParameter] public string $refreshToken,
    ) {
        foreach ([$clientId, $clientSecret, $refreshToken] as $value) {
            if ($value === '') {
                throw new \InvalidArgumentException('OAuth request fields must be nonempty strings.');
            }
        }
    }

    public function __debugInfo(): array { return []; }
    public function jsonSerialize(): array { return $this->__debugInfo(); }
    public function __serialize(): array { throw new \LogicException('OAuth requests cannot be serialized.'); }
}
