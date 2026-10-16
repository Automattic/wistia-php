<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Account\ExpiringAccessToken;
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class CreateExpiringAccessTokenResult implements Result, \JsonSerializable
{
    public function __construct(#[\SensitiveParameter] public ExpiringAccessToken $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(ExpiringAccessToken::fromWire($value), $status);
    }
    public function __debugInfo(): array { return []; }
    public function jsonSerialize(): array { return ['status' => $this->status]; }
    public function __serialize(): array { throw new \LogicException('Access tokens cannot be serialized.'); }
}
