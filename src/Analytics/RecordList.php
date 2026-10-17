<?php
declare(strict_types=1);

namespace Automattic\Wistia\Analytics;

/** @internal Typed media Analytics lists without private arrays in internal mapping traces. */
final class RecordList
{
    /**
     * @template T
     * @param callable(mixed): T $decode
     * @return list<T>
     */
    public static function decode(#[\SensitiveParameter] mixed $value, #[\SensitiveParameter] callable $decode): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw new \UnexpectedValueException('Expected an Analytics record list.');
        }
        $records = [];
        // array_map would retain its unannotated input arguments if a record decoder throws.
        foreach ($value as $item) { $records[] = $decode($item); }
        return $records;
    }
}
