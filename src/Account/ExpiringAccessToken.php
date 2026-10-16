<?php
declare(strict_types=1);

namespace Automattic\Wistia\Account;

use Automattic\Wistia\Media\Wire;

final readonly class ExpiringAccessToken implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public string $token,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['token'], ['token']);
        return new self(
            Wire::string($value->token),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
    public function jsonSerialize(): array { return []; }
    public function __serialize(): array { throw new \LogicException('Access tokens cannot be serialized.'); }
}
