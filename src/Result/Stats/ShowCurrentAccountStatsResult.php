<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Stats;

use Automattic\Wistia\Stats\AccountStats;
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class ShowCurrentAccountStatsResult implements Result
{
    /** @param AccountStats $data */
    public function __construct(#[\SensitiveParameter] public AccountStats $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(AccountStats::fromWire($value), $status);
    }
    public function __debugInfo(): array { return []; }
}
