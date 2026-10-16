<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Folders\{CreateFolderSharingData};
use Automattic\Wistia\Result\Result;

final readonly class CreateFolderSharingResult implements Result
{
    public function __construct(#[\SensitiveParameter] public CreateFolderSharingData $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        return new self(match ($status) {
            201 => CreateFolderSharingData::fromWire($value),
            default => throw new \UnexpectedValueException('Unexpected endpoint status.'),
        }, $status);
    }
    public function __debugInfo(): array { return []; }
}
