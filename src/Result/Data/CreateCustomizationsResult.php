<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Result\Result;
use Automattic\Wistia\Customizations\ShowCustomizationsData;

final readonly class CreateCustomizationsResult implements Result
{
    public function __construct(#[\SensitiveParameter] public ShowCustomizationsData $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 201) { throw new \UnexpectedValueException("Unexpected endpoint status."); }
        return new self(ShowCustomizationsData::fromWire($value), $status);
    }
    public function __debugInfo(): array { return []; }
}
