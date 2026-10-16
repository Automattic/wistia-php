<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Folders\{BulkDeleteSubfoldersData};
use Automattic\Wistia\Result\Result;

final readonly class BulkDeleteSubfoldersResult implements Result
{
    public function __construct(#[\SensitiveParameter] public BulkDeleteSubfoldersData $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        return new self(match ($status) {
            200 => BulkDeleteSubfoldersData::fromWire($value),
            default => throw new \UnexpectedValueException('Unexpected endpoint status.'),
        }, $status);
    }
    public function __debugInfo(): array { return []; }
}
