<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Accessibility\{GetOrderStatusData};
use Automattic\Wistia\Result\Result;

final readonly class GetOrderStatusResult implements Result
{
    public function __construct(#[\SensitiveParameter] public GetOrderStatusData $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(GetOrderStatusData::fromWire($value), $status);
    }
    public function __debugInfo(): array { return []; }
}
