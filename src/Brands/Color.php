<?php
declare(strict_types=1);
namespace Automattic\Wistia\Brands;

/** @internal Color decoding and local syntax checks; normalization belongs to Wistia. */
final class Color
{
    public static function validate(#[\SensitiveParameter] string $value): void
    {
        if (preg_match('/\A#?(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})\z/D', $value) !== 1) {
            throw new \InvalidArgumentException('Supply a three- or six-digit hex brand color.');
        }
    }
    public static function fromWire(#[\SensitiveParameter] mixed $value): string|Gradient
    {
        if (is_string($value)) { return $value; }
        return Gradient::fromWire($value);
    }
}
