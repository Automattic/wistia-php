<?php
declare(strict_types=1);

namespace Automattic\Wistia\Sharing;

use Automattic\Wistia\Media\Wire;

/** @internal Decode the documented metadata value union without exposing raw JSON objects. */
final class MetadataValue
{
    /** @return string|float|bool|list<string|ContactReference>|ContactReference|null */
    public static function decode(#[\SensitiveParameter] mixed $value): string|float|bool|array|ContactReference|null
    {
        if ($value === null || is_string($value) || is_bool($value)) { return $value; }
        if (is_int($value) || is_float($value)) { return Wire::number($value); }
        if ($value instanceof \stdClass) { return ContactReference::fromWire($value); }
        return Wire::items($value, static fn($item) => is_string($item) ? $item : ContactReference::fromWire($item));
    }
}
