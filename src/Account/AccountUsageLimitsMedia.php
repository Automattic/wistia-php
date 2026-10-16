<?php
declare(strict_types=1);

namespace Automattic\Wistia\Account;

use Automattic\Wistia\Media\Wire;

final readonly class AccountUsageLimitsMedia
{
    public function __construct(
        #[\SensitiveParameter] public int $count,
        #[\SensitiveParameter] public int|null $flexible_limit,
        #[\SensitiveParameter] public int|null $inflexible_limit,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['count', 'flexible_limit', 'inflexible_limit'], ['count', 'flexible_limit', 'inflexible_limit']);
        return new self(
            Wire::integer($value->count),
            ($value->flexible_limit === null ? null : Wire::integer($value->flexible_limit)),
            ($value->inflexible_limit === null ? null : Wire::integer($value->inflexible_limit)),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
