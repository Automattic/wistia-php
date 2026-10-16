<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Folders\{ListFoldersDataItem};
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class ListFoldersResult implements Result
{
    /** @param list<ListFoldersDataItem> $data */
    public function __construct(#[\SensitiveParameter] public array $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        return new self(match ($status) {
            200 => Wire::items($value, static fn($v) => ListFoldersDataItem::fromWire($v)),
            default => throw new \UnexpectedValueException('Unexpected endpoint status.'),
        }, $status);
    }
    public function __debugInfo(): array { return []; }
}
