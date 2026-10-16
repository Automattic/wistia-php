<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Media\{ListTagsDataItem, Wire};
use Automattic\Wistia\Result\Result;

final readonly class ListTagsResult implements Result
{
    /** @param list<ListTagsDataItem> $data */
    public function __construct(#[\SensitiveParameter] public array $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        return new self(match ($status) {
            200 => Wire::items($value, static fn($v) => ListTagsDataItem::fromWire($v)),
            default => throw new \UnexpectedValueException('Unexpected endpoint status.'),
        }, $status);
    }
    public function __debugInfo(): array { return []; }
}
