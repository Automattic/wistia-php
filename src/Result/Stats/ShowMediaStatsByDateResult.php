<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Stats;

use Automattic\Wistia\Stats\DailyStats;
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class ShowMediaStatsByDateResult implements Result
{
    /** @param list<DailyStats> $data */
    public function __construct(#[\SensitiveParameter] public array $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(Wire::items($value, DailyStats::fromWire(...)), $status);
    }
    public function __debugInfo(): array { return []; }
}
