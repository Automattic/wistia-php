<?php
declare(strict_types=1);

namespace Automattic\Wistia\Channels;

/** @internal Local syntax checks only; Wistia owns the remote schedule. */
final class Scheduling
{
    public static function timestamp(#[\SensitiveParameter] string $value): void
    {
        if (!preg_match('/\A\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?Z\z/D', $value)) {
            throw new \InvalidArgumentException('Supply an ISO8601 UTC timestamp ending in Z.');
        }
        try {
            new \DateTimeImmutable($value);
            $errors = \DateTimeImmutable::getLastErrors();
            if ($errors !== false && ($errors['warning_count'] || $errors['error_count'])) {
                throw new \InvalidArgumentException();
            }
        } catch (\Throwable) {
            throw new \InvalidArgumentException('Supply a valid ISO8601 UTC timestamp.');
        }
    }

    public static function creation(?string $status, #[\SensitiveParameter] ?string $at): void
    {
        if (($status === 'scheduled') !== ($at !== null)) {
            throw new \InvalidArgumentException('Creating a scheduled episode requires publish_at; other creation statuses must omit it.');
        }
    }
}
