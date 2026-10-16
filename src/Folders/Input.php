<?php
declare(strict_types=1);

namespace Automattic\Wistia\Folders;

/** @internal Local string limits; authorization and entitlement remain remote. */
final class Input
{
    public static function length(#[\SensitiveParameter] string $value, int $max): void
    {
        $length = preg_match_all('/./us', $value);
        if ($length === false || $length > $max) {
            throw new \InvalidArgumentException('Text exceeds the documented character limit or is not valid UTF-8.');
        }
    }
}
