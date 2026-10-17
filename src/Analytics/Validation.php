<?php
declare(strict_types=1);

namespace Automattic\Wistia\Analytics;

/** @internal Calendar checks only; retention and account access remain server-owned. */
final class Validation
{
    public static function date(#[\SensitiveParameter] string $value): void
    {
        if (preg_match('/\A([0-9]{4})-([0-9]{2})-([0-9]{2})\z/', $value, $parts) !== 1
            || !checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])) {
            throw new \InvalidArgumentException('Analytics dates must be valid YYYY-MM-DD calendar dates.');
        }
    }
    public static function range(#[\SensitiveParameter] ?string $start, #[\SensitiveParameter] ?string $end, bool $limitTwoYears = true): void
    {
        if ($start !== null) { self::date($start); }
        if ($end !== null) { self::date($end); }
        if ($start === null || $end === null) { return; }
        if ($end <= $start) {
            throw new \InvalidArgumentException('Analytics end_date must be after start_date; the end is exclusive.');
        }
        if ($limitTwoYears) {
            // Clamp leap day to the last day of February in the target calendar year.
            [$year, $month, $day] = array_map('intval', explode('-', $start));
            $year += 2;
            while (!checkdate($month, $day, $year)) { --$day; }
            if ($end > sprintf('%04d-%02d-%02d', $year, $month, $day)) {
                throw new \InvalidArgumentException('Analytics date ranges must not exceed two calendar years.');
            }
        }
    }
    public static function perPage(?int $value, int $max): void
    {
        if ($value !== null && ($value < 1 || $value > $max)) {
            throw new \InvalidArgumentException('per_page is outside this endpoint’s limits.');
        }
    }
    public static function embedUrl(#[\SensitiveParameter] string $value): void
    {
        // Wistia accepts a schemeless domain/path. Validate without rewriting the sent bytes.
        $hasScheme = preg_match('/\A[A-Za-z][A-Za-z0-9+.-]*:\/\//', $value) === 1;
        \Automattic\Wistia\Media\Input::url($hasScheme ? $value : 'https://' . $value);
    }
}
