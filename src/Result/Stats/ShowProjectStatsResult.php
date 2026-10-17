<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Stats;

use Automattic\Wistia\Stats\ProjectStats;
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class ShowProjectStatsResult implements Result
{
    /** @param ProjectStats $data */
    public function __construct(#[\SensitiveParameter] public ProjectStats $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(ProjectStats::fromWire($value), $status);
    }
    public function __debugInfo(): array { return []; }
}
