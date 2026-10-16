<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Folders\{ListSubfoldersDataItem};
use Automattic\Wistia\Result\Result;

final readonly class UpdateSubfolderResult implements Result
{
    public function __construct(#[\SensitiveParameter] public ListSubfoldersDataItem $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        return new self(match ($status) {
            200 => ListSubfoldersDataItem::fromWire($value),
            default => throw new \UnexpectedValueException('Unexpected endpoint status.'),
        }, $status);
    }
    public function __debugInfo(): array { return []; }
}
