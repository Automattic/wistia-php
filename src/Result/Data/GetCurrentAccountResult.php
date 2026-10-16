<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Account\CurrentAccount;
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class GetCurrentAccountResult implements Result
{
    public function __construct(#[\SensitiveParameter] public CurrentAccount $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(CurrentAccount::fromWire($value), $status);
    }
    public function __debugInfo(): array { return []; }
}
