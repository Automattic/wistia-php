<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\OAuth;

use Automattic\Wistia\Http\RateLimitInfo;
use Automattic\Wistia\Result\Result;

/** Credentials are deliberately readable; applications explicitly persist selected fields. */
final readonly class TokenResult implements Result, \JsonSerializable
{
    /** @param list<string> $scope Exact returned scopes, without splitting or normalization. */
    public function __construct(
        #[\SensitiveParameter] public string $accessToken,
        #[\SensitiveParameter] public string $tokenType,
        public int $expiresIn,
        #[\SensitiveParameter] public string $refreshToken,
        #[\SensitiveParameter] public array $scope,
        public int $status,
        public RateLimitInfo $rateLimit,
    ) {
        if ($accessToken === '' || $tokenType === '' || $refreshToken === '' || $expiresIn < 0 || !array_is_list($scope)) {
            throw new \InvalidArgumentException('Invalid OAuth token fields.');
        }
        foreach ($scope as $item) {
            if (!is_string($item)) { throw new \InvalidArgumentException('OAuth scopes must be strings.'); }
        }
    }

    public function __debugInfo(): array { return ['status' => $this->status]; }
    public function jsonSerialize(): array { return $this->__debugInfo(); }
    public function __serialize(): array { throw new \LogicException('OAuth credentials cannot be serialized.'); }
}
