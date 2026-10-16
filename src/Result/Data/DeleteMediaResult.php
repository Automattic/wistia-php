<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Media\{DeleteMediaData};
use Automattic\Wistia\Result\Result;

final readonly class DeleteMediaResult implements Result
{
    public function __construct(#[\SensitiveParameter] public DeleteMediaData $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        return new self(match ($status) {
            200 => DeleteMediaData::fromWire($value),
            default => throw new \UnexpectedValueException('Unexpected endpoint status.'),
        }, $status);
    }
    public function __debugInfo(): array { return []; }
}
