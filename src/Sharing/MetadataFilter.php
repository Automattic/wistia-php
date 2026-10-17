<?php
declare(strict_types=1);

namespace Automattic\Wistia\Sharing;

use Automattic\Wistia\Media\Input;

/** One custom metadata field filter. Values retain their documented wire strings. */
final readonly class MetadataFilter
{
    /** @param list<array{string, string}> $suffixes */
    private function __construct(#[\SensitiveParameter] private array $suffixes) {}
    public static function exact(#[\SensitiveParameter] string $value): self { return new self([['', $value]]); }
    /** @param list<string> $values */
    public static function values(#[\SensitiveParameter] array $values): self
    {
        Input::strings($values); Input::count($values, 1);
        return new self(array_map(static fn(string $v) => ['[]', $v], $values));
    }
    public static function numericRange(#[\SensitiveParameter] ?string $min = null, #[\SensitiveParameter] ?string $max = null): self
    {
        if (($min === null && $max === null) || ($min !== null && !is_numeric($min)) || ($max !== null && !is_numeric($max))) {
            throw new \InvalidArgumentException('Supply at least one numeric range bound.');
        }
        return new self([...($min === null ? [] : [['[min]', $min]]), ...($max === null ? [] : [['[max]', $max]])]);
    }
    public static function dateRange(#[\SensitiveParameter] ?string $after = null, #[\SensitiveParameter] ?string $before = null): self
    {
        if ($after === null && $before === null) { throw new \InvalidArgumentException('Supply at least one date range bound.'); }
        foreach ([$after, $before] as $bound) {
            if ($bound === null) { continue; }
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/D', $bound)) {
                $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $bound);
                if ($date === false || $date->format('Y-m-d') !== $bound) { throw new \InvalidArgumentException('Invalid date.'); }
            } else { Validation::timestamp($bound); }
        }
        return new self([...($after === null ? [] : [['[after]', $after]]), ...($before === null ? [] : [['[before]', $before]])]);
    }
    public static function exists(bool $exists): self { return new self([['[exists]', $exists ? 'true' : 'false']]); }
    /** @internal @return list<array{string, string}> */
    public function pairs(#[\SensitiveParameter] string $field): array
    {
        return array_map(static fn(array $pair) => ['custom_metadata[' . $field . ']' . $pair[0], $pair[1]], $this->suffixes);
    }
    public function __debugInfo(): array { return []; }
}
