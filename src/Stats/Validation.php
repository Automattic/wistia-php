<?php
declare(strict_types=1);

namespace Automattic\Wistia\Stats;

/** @internal Local calendar validation; account access and data availability remain remote. */
final class Validation
{
    public static function date(#[\SensitiveParameter] string $value): void
    {
        if (preg_match('/\A([0-9]{4})-([0-9]{2})-([0-9]{2})\z/', $value, $parts) !== 1
            || !checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])) {
            throw new \InvalidArgumentException('Stats dates must be valid YYYY-MM-DD calendar dates.');
        }
    }
    public static function range(#[\SensitiveParameter] ?string $start, #[\SensitiveParameter] ?string $end): void
    {
        if ($start !== null && $end !== null && $start > $end) {
            throw new \InvalidArgumentException('Stats end_date must be on or after start_date; both bounds are inclusive.');
        }
    }
}
