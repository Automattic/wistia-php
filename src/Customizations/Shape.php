<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** @internal Source-schema checks; Client sanitizes response failures. Never include field values. */
final class Shape
{
    public static function encode(#[\SensitiveParameter] mixed $value): mixed
    {
        if ($value instanceof \JsonSerializable) { return self::encode($value->jsonSerialize()); }
        if (is_array($value)) { return array_map(self::encode(...), $value); }
        return $value;
    }

    /** Ordered [hex color, finite numeric stop] pairs; no undocumented stop bounds. */
    public static function gradient(#[\SensitiveParameter] array $colors): void
    {
        foreach ($colors as $pair) {
            if (!is_array($pair) || !array_is_list($pair) || count($pair) !== 2
                || !is_string($pair[0]) || preg_match('/\A#?(?:[0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})\z/iD', $pair[0]) !== 1
                || (!is_int($pair[1]) && !is_float($pair[1])) || !is_finite((float) $pair[1])) {
                throw new \InvalidArgumentException('Gradient entries must be [hex color, finite numeric stop] pairs.');
            }
        }
    }

    public static function check(#[\SensitiveParameter] mixed $value, array $schema, bool $resets = false): void
    {
        $branches = $schema['oneOf'] ?? $schema['anyOf'] ?? null;
        if ($branches !== null) {
            foreach ($branches as $branch) {
                try { self::check($value, $branch, $resets); return; }
                catch (\InvalidArgumentException) {}
            }
            throw new \InvalidArgumentException('Invalid customization value shape.');
        }
        $valid = match ($schema['type'] ?? null) {
            'object' => $value instanceof \stdClass,
            'array' => is_array($value) && array_is_list($value),
            'string' => is_string($value),
            'boolean' => is_bool($value),
            'integer' => is_int($value),
            'number' => (is_int($value) || is_float($value)) && is_finite((float) $value),
            default => true,
        };
        if (!$valid || (isset($schema['enum']) && !in_array($value, $schema['enum'], true))) {
            throw new \InvalidArgumentException('Invalid customization type or option.');
        }
        if (($schema['type'] ?? null) === 'object') {
            $props = $schema['properties'] ?? [];
            $keys = array_keys(get_object_vars($value));
            if (array_diff($schema['required'] ?? [], $keys)
                || (($schema['unevaluatedProperties'] ?? true) === false && array_diff($keys, array_keys($props)))) {
                throw new \InvalidArgumentException('Invalid customization object fields.');
            }
            foreach ($props as $key => $child) {
                if (property_exists($value, $key) && !($resets && $value->$key === null)) {
                    self::check($value->$key, $child, $resets);
                }
            }
        }
        if (($schema['type'] ?? null) === 'array' && isset($schema['items'])) {
            foreach ($value as $item) { self::check($item, $schema['items'], $resets); }
        }
    }
}
