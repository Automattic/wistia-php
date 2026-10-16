<?php
declare(strict_types=1);

namespace Automattic\Wistia\Account;

use Automattic\Wistia\Media\Wire;

final readonly class AccountUsage
{
    public function __construct(
        #[\SensitiveParameter] public AccountUsagePlan $plan,
        #[\SensitiveParameter] public bool $can_upload,
        #[\SensitiveParameter] public string|null $upload_blocked_reason,
        #[\SensitiveParameter] public AccountUsageLimits|null $limits,
        #[\SensitiveParameter] public AccountUsageLinks $links,
        #[\SensitiveParameter] public string|null $role,
        #[\SensitiveParameter] public bool $verified_domain,
        #[\SensitiveParameter] public bool $can_invite_teammates,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['plan', 'can_upload', 'upload_blocked_reason', 'limits', 'links', 'role', 'verified_domain', 'can_invite_teammates'], ['plan', 'can_upload', 'upload_blocked_reason', 'limits', 'links', 'role', 'verified_domain', 'can_invite_teammates']);
        return new self(
            AccountUsagePlan::fromWire($value->plan),
            Wire::boolean($value->can_upload),
            ($value->upload_blocked_reason === null ? null : Wire::string($value->upload_blocked_reason)),
            ($value->limits === null ? null : AccountUsageLimits::fromWire($value->limits)),
            AccountUsageLinks::fromWire($value->links),
            ($value->role === null ? null : Wire::string($value->role)),
            Wire::boolean($value->verified_domain),
            Wire::boolean($value->can_invite_teammates),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
