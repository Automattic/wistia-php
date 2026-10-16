<?php
declare(strict_types=1);
namespace Automattic\Wistia\Accessibility;

/** @internal Detectable local time-window errors; matching remains server-owned. */
final class Validation
{
    public static function window(?int $start, ?int $end): void
    {
        if (($start === null) !== ($end === null) || ($start !== null && ($start < 0 || $end < $start))) {
            throw new \InvalidArgumentException('Supply both time bounds, with 0 <= start <= end.');
        }
    }
}
