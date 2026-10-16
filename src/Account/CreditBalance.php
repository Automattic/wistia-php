<?php
declare(strict_types=1);

namespace Automattic\Wistia\Account;

use Automattic\Wistia\Media\Wire;

final readonly class CreditBalance
{
    public function __construct(
        #[\SensitiveParameter] public string $balance,
        #[\SensitiveParameter] public string|null $next_grant_at,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['balance', 'next_grant_at'], ['balance', 'next_grant_at']);
        return new self(
            self::decimal($value->balance),
            ($value->next_grant_at === null ? null : Wire::string($value->next_grant_at)),
            array_keys(get_object_vars($value)),
        );
    }
    private static function decimal(#[\SensitiveParameter] mixed $value): string
    {
        $value = Wire::string($value);
        if (preg_match('/\A[0-9]+(?:\.[0-9]+)?\z/D', $value) !== 1) { throw new \UnexpectedValueException('Expected an exact nonnegative decimal string.'); }
        return $value;
    }
    public function __debugInfo(): array { return []; }
}
