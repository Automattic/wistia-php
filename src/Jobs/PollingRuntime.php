<?php
declare(strict_types=1);

namespace Automattic\Wistia\Jobs;

/** Monotonic clock and interruptible wait seam. Implementations must not issue HTTP. */
interface PollingRuntime
{
    public function now(): float;
    public function wait(float $seconds): void;
}
