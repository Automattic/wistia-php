<?php
declare(strict_types=1);

namespace Automattic\Wistia\Account;

use Automattic\Wistia\Media\Wire;

final readonly class AccountUsagePlan
{
    public function __construct(
        #[\SensitiveParameter] public string $tier,
        #[\SensitiveParameter] public string $name,
        #[\SensitiveParameter] public bool $is_standard,
        #[\SensitiveParameter] public bool $is_trial,
        #[\SensitiveParameter] public string|null $trial_expires_at,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['tier', 'name', 'is_standard', 'is_trial', 'trial_expires_at'], ['tier', 'name', 'is_standard', 'is_trial', 'trial_expires_at']);
        return new self(
            Wire::string($value->tier),
            Wire::string($value->name),
            Wire::boolean($value->is_standard),
            Wire::boolean($value->is_trial),
            ($value->trial_expires_at === null ? null : Wire::string($value->trial_expires_at)),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
