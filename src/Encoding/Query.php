<?php
declare(strict_types=1);

namespace Automattic\Wistia\Encoding;

/** Ordered exact-name pairs: repeated bracketed keys are not numeric array indexes. */
final readonly class Query
{
    /** @param list<array{0: string, 1: string|int|float|bool|null}> $pairs */
    public function __construct(#[\SensitiveParameter] private array $pairs = [])
    {
        foreach ($pairs as $pair) {
            if (!is_array($pair) || !array_is_list($pair) || count($pair) !== 2 || !is_string($pair[0])
                || !(is_scalar($pair[1]) || $pair[1] === null)
                || (is_float($pair[1]) && !is_finite($pair[1]))) {
                throw new \InvalidArgumentException('Query entries must be exact-name scalar pairs.');
            }
        }
    }

    public function encode(): string
    {
        $pairs = [];
        foreach ($this->pairs as [$name, $value]) {
            // Null is explicit name=; omission means no pair. JSON retains literal null instead.
            $value = match (true) { $value === true => 'true', $value === false => 'false', $value === null => '', default => (string) $value };
            $pairs[] = rawurlencode($name) . '=' . rawurlencode($value);
        }
        return implode('&', $pairs);
    }

    public function __debugInfo(): array { return []; }
}
