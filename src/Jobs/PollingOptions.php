<?php
declare(strict_types=1);

namespace Automattic\Wistia\Jobs;

final readonly class PollingOptions
{
    /** Interval is a local policy, not a server Retry-After promise. Attempts include the first read. */
    public function __construct(public int $maxAttempts = 10, public float $intervalSeconds = 1.0)
    {
        if ($maxAttempts < 1 || !is_finite($intervalSeconds) || $intervalSeconds < 0 || $intervalSeconds > 86400) {
            throw new \InvalidArgumentException('Polling needs a positive attempt bound and an interval between zero and 86400 seconds.');
        }
    }
}
