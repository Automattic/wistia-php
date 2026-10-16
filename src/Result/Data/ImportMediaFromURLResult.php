<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Media\{ImportMediaFromURLData};
use Automattic\Wistia\Result\Result;

final readonly class ImportMediaFromURLResult implements Result
{
    public function __construct(#[\SensitiveParameter] public ImportMediaFromURLData $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        return new self(match ($status) {
            201 => ImportMediaFromURLData::fromWire($value),
            default => throw new \UnexpectedValueException('Unexpected endpoint status.'),
        }, $status);
    }
    public function __debugInfo(): array { return []; }
}
