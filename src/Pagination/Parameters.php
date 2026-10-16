<?php
declare(strict_types=1);

namespace Automattic\Wistia\Pagination;

use Automattic\Wistia\Encoding\Query;

/** Internal exact-name filter validation; adapters own all pagination controls. */
final class Parameters
{
    public static function validate(#[\SensitiveParameter] array $filters): void
    {
        new Query($filters);
        foreach ($filters as [$name]) {
            if (preg_match('/^(page|per_page|cursor)(?:\[|$)/D', $name)) {
                throw new \InvalidArgumentException('Pagination controls must be passed to the adapter, not filters.');
            }
        }
    }
    public static function size(int $perPage): void
    {
        if ($perPage < 1 || $perPage > 100) { throw new \InvalidArgumentException('Page size must be between 1 and 100.'); }
    }
    public static function cursor(#[\SensitiveParameter] int|string|null $position): void
    {
        if ($position !== null && (!is_string($position) || $position === '')) {
            throw new \InvalidArgumentException('A cursor must be a nonempty string or null.');
        }
    }
    public static function advance(#[\SensitiveParameter] ?string $cursor): string
    {
        if ($cursor === null || $cursor === '') { throw new \UnexpectedValueException('A continuing page requires a cursor.'); }
        return $cursor;
    }
}
