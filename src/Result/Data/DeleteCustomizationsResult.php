<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Result\Result;

final readonly class DeleteCustomizationsResult implements Result
{
    public function __construct(public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException("Unexpected endpoint status."); }
        return new self($status);
    }
    public function __debugInfo(): array { return []; }
}
