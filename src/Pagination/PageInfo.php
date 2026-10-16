<?php
declare(strict_types=1);

namespace Automattic\Wistia\Pagination;

/** Internal decoder for documented scalar-cursor envelopes, not arbitrary resource schemas. */
final class PageInfo
{
    public static function fromEnvelope(#[\SensitiveParameter] object $envelope, string $itemsKey): Page
    {
        $items = $envelope->$itemsKey ?? null; $info = $envelope->page_info ?? null;
        if (!is_array($items) || !array_is_list($items) || !$info instanceof \stdClass
            || !isset($info->has_next_page, $info->has_previous_page)
            || !is_bool($info->has_next_page) || !is_bool($info->has_previous_page)
            || !property_exists($info, 'end_cursor') || !property_exists($info, 'start_cursor')
            || !($info->end_cursor === null || is_string($info->end_cursor))
            || !($info->start_cursor === null || is_string($info->start_cursor))) {
            throw new \UnexpectedValueException('Invalid pagination envelope.');
        }
        return new Page($items, $info->end_cursor, $info->has_next_page, $info->start_cursor, $info->has_previous_page);
    }
}
