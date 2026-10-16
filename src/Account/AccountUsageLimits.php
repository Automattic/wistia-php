<?php
declare(strict_types=1);

namespace Automattic\Wistia\Account;

use Automattic\Wistia\Media\Wire;

final readonly class AccountUsageLimits
{
    public function __construct(
        #[\SensitiveParameter] public string $primary_resource,
        #[\SensitiveParameter] public AccountUsageLimitsMedia $media,
        #[\SensitiveParameter] public AccountUsageLimitsStorage $storage,
        #[\SensitiveParameter] public AccountUsageLimitsUsers $users,
        #[\SensitiveParameter] public AccountUsageLimitsBandwidth $bandwidth,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['primary_resource', 'media', 'storage', 'users', 'bandwidth'], ['primary_resource', 'media', 'storage', 'users', 'bandwidth']);
        return new self(
            Wire::oneOf(Wire::string($value->primary_resource), ['storage', 'media']),
            AccountUsageLimitsMedia::fromWire($value->media),
            AccountUsageLimitsStorage::fromWire($value->storage),
            AccountUsageLimitsUsers::fromWire($value->users),
            AccountUsageLimitsBandwidth::fromWire($value->bandwidth),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
