<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Stats;

use Automattic\Wistia\Stats\MediaStats;
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class ShowMediaStatsResult implements Result
{
    /** @param MediaStats $data */
    public function __construct(#[\SensitiveParameter] public MediaStats $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(MediaStats::fromWire($value), $status);
    }
    public function __debugInfo(): array { return []; }
}
