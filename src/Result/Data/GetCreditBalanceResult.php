<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Account\CreditBalance;
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class GetCreditBalanceResult implements Result
{
    public function __construct(#[\SensitiveParameter] public CreditBalance $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(CreditBalance::fromWire($value), $status);
    }
    public function __debugInfo(): array { return []; }
}
