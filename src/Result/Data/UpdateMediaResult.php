<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Media\{UpdateMediaData};
use Automattic\Wistia\Result\Result;

final readonly class UpdateMediaResult implements Result
{
    public function __construct(#[\SensitiveParameter] public UpdateMediaData $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        return new self(match ($status) {
            200 => UpdateMediaData::fromWire($value),
            default => throw new \UnexpectedValueException('Unexpected endpoint status.'),
        }, $status);
    }
    public function __debugInfo(): array { return []; }
}
