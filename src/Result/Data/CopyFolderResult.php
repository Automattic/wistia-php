<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Folders\{CopyFolderData};
use Automattic\Wistia\Result\Result;

final readonly class CopyFolderResult implements Result
{
    public function __construct(#[\SensitiveParameter] public CopyFolderData $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        return new self(match ($status) {
            202 => CopyFolderData::fromWire($value),
            default => throw new \UnexpectedValueException('Unexpected endpoint status.'),
        }, $status);
    }
    public function __debugInfo(): array { return []; }
}
