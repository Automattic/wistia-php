<?php
declare(strict_types=1);

namespace Automattic\Wistia\Account;

use Automattic\Wistia\Media\Wire;

final readonly class AccountUsageLinks
{
    public function __construct(
        #[\SensitiveParameter] public string $plans_url,
        #[\SensitiveParameter] public string|null $usage_url,
        #[\SensitiveParameter] public string|null $billing_url,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['plans_url', 'usage_url', 'billing_url'], ['plans_url', 'usage_url', 'billing_url']);
        return new self(
            Wire::string($value->plans_url),
            ($value->usage_url === null ? null : Wire::string($value->usage_url)),
            ($value->billing_url === null ? null : Wire::string($value->billing_url)),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
