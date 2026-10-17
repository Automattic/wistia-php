<?php
declare(strict_types=1);

namespace Automattic\Wistia\Sharing;

/** @internal Local search syntax; field definitions and entitlements remain remote checks. */
final class Validation
{
    public static function utc(#[\SensitiveParameter] string $value): void
    {
        if (!str_ends_with($value, 'Z')) { throw new \InvalidArgumentException('Use an ISO 8601 UTC timestamp ending in Z.'); }
        self::timestamp($value);
    }
    public static function timestamp(#[\SensitiveParameter] string $value): void
    {
        if (!preg_match('/\A\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-](?:0\d|1\d|2[0-3]):[0-5]\d)\z/D', $value)) {
            throw new \InvalidArgumentException('Use an ISO 8601 timestamp with a time zone.');
        }
        try { new \DateTimeImmutable($value); $errors = \DateTimeImmutable::getLastErrors(); }
        catch (\Exception) { throw new \InvalidArgumentException('Invalid timestamp.'); }
        if ($errors !== false && ($errors['warning_count'] || $errors['error_count'])) {
            throw new \InvalidArgumentException('Invalid timestamp.');
        }
    }
}
