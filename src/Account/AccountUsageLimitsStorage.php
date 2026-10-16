<?php
declare(strict_types=1);

namespace Automattic\Wistia\Account;

use Automattic\Wistia\Media\Wire;

final readonly class AccountUsageLimitsStorage
{
    public function __construct(
        #[\SensitiveParameter] public int $used_bytes,
        #[\SensitiveParameter] public int|null $flexible_limit_bytes,
        #[\SensitiveParameter] public int|null $inflexible_limit_bytes,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['used_bytes', 'flexible_limit_bytes', 'inflexible_limit_bytes'], ['used_bytes', 'flexible_limit_bytes', 'inflexible_limit_bytes']);
        return new self(
            Wire::integer($value->used_bytes),
            ($value->flexible_limit_bytes === null ? null : Wire::integer($value->flexible_limit_bytes)),
            ($value->inflexible_limit_bytes === null ? null : Wire::integer($value->inflexible_limit_bytes)),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
