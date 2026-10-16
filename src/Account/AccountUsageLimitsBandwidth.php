<?php
declare(strict_types=1);

namespace Automattic\Wistia\Account;

use Automattic\Wistia\Media\Wire;

final readonly class AccountUsageLimitsBandwidth
{
    public function __construct(
        #[\SensitiveParameter] public int $used_bytes,
        #[\SensitiveParameter] public int|null $limit_bytes,
        #[\SensitiveParameter] public string $period,
        #[\SensitiveParameter] public string|null $resets_at,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['used_bytes', 'limit_bytes', 'period', 'resets_at'], ['used_bytes', 'limit_bytes', 'period', 'resets_at']);
        return new self(
            Wire::integer($value->used_bytes),
            ($value->limit_bytes === null ? null : Wire::integer($value->limit_bytes)),
            Wire::oneOf(Wire::string($value->period), ['monthly', 'annual']),
            ($value->resets_at === null ? null : Wire::string($value->resets_at)),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
