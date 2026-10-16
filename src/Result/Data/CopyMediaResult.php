<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Media\{CopyMediaData};
use Automattic\Wistia\Result\Result;

final readonly class CopyMediaResult implements Result
{
    public function __construct(#[\SensitiveParameter] public CopyMediaData $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        return new self(match ($status) {
            201 => CopyMediaData::fromWire($value),
            default => throw new \UnexpectedValueException('Unexpected endpoint status.'),
        }, $status);
    }
    public function __debugInfo(): array { return []; }
}
