<?php
declare(strict_types=1);

namespace Automattic\Wistia\Webinars;

/** @internal Local syntax only; entitlement and webinar state remain remote checks. */
final class Validation
{
    public static function scheduled_for(#[\SensitiveParameter] string $value): void
    {
        if (!preg_match('/\A\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|\+00:00)\z/D', $value)) {
            throw new \InvalidArgumentException('Use an ISO 8601 UTC timestamp ending in Z or +00:00.');
        }
        try { new \DateTimeImmutable($value); $errors = \DateTimeImmutable::getLastErrors(); }
        catch (\Exception) { throw new \InvalidArgumentException('Invalid webinar schedule timestamp.'); }
        if ($errors !== false && ($errors['warning_count'] || $errors['error_count'])) {
            throw new \InvalidArgumentException('Invalid webinar schedule timestamp.');
        }
    }
    public static function time_zone(#[\SensitiveParameter] string $value): void
    {
        if (!in_array($value, \DateTimeZone::listIdentifiers(\DateTimeZone::ALL_WITH_BC), true)) {
            throw new \InvalidArgumentException('Use an IANA time zone identifier.');
        }
    }
    public static function event_duration(int $value): void
    {
        if ($value < 15) { throw new \InvalidArgumentException('Webinars must last at least 15 minutes.'); }
    }
    public static function email(#[\SensitiveParameter] string $value): void
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('A valid email address is required.');
        }
    }
}
