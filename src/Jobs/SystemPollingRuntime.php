<?php
declare(strict_types=1);

namespace Automattic\Wistia\Jobs;

final class SystemPollingRuntime implements PollingRuntime
{
    public function now(): float { [$seconds, $nanos] = hrtime(); return $seconds + $nanos / 1_000_000_000; }
    public function wait(float $seconds): void
    {
        $whole = (int) $seconds;
        $remaining = time_nanosleep($whole, (int) (($seconds - $whole) * 1_000_000_000));
        while (is_array($remaining)) { $remaining = time_nanosleep($remaining['seconds'], $remaining['nanoseconds']); }
        if ($remaining === false) { throw new \RuntimeException('Polling wait failed.'); }
    }
}
