<?php
declare(strict_types=1);

namespace Automattic\Wistia\Media;

/** @internal Validation shared only by the media lifecycle response records. */
final class Wire
{
    public static function object(#[\SensitiveParameter] mixed $value, array $allowed, array $required): void
    {
        if (!$value instanceof \stdClass
            || array_diff(array_keys(get_object_vars($value)), $allowed) !== []
            || array_diff($required, array_keys(get_object_vars($value))) !== []) {
            throw new \UnexpectedValueException('Invalid media response object.');
        }
    }
    public static function string(#[\SensitiveParameter] mixed $v): string
    {
        if (!is_string($v)) { throw new \UnexpectedValueException('Expected a string.'); }
        return $v;
    }
    public static function integer(#[\SensitiveParameter] mixed $v): int
    {
        if (!is_int($v)) { throw new \UnexpectedValueException('Expected a representable integer.'); }
        return $v;
    }
    public static function number(#[\SensitiveParameter] mixed $v): float
    {
        if ((!is_float($v) && !is_int($v)) || !is_finite((float) $v)) { throw new \UnexpectedValueException('Expected a finite number.'); }
        return (float) $v;
    }
    public static function boolean(#[\SensitiveParameter] mixed $v): bool
    {
        if (!is_bool($v)) { throw new \UnexpectedValueException('Expected a boolean.'); }
        return $v;
    }
    public static function items(#[\SensitiveParameter] mixed $v, callable $decode): array
    {
        if (!is_array($v) || !array_is_list($v)) { throw new \UnexpectedValueException('Expected a list.'); }
        return array_map($decode, $v);
    }
    public static function oneOf(#[\SensitiveParameter] mixed $v, array $allowed): mixed
    {
        if (!in_array($v, $allowed, true)) { throw new \UnexpectedValueException('Unexpected enum value.'); }
        return $v;
    }
    public static function range(float|int $v, float|int|null $min, float|int|null $max): float|int
    {
        if (($min !== null && $v < $min) || ($max !== null && $v > $max)) {
            throw new \UnexpectedValueException('Number outside documented range.');
        }
        return $v;
    }
}
