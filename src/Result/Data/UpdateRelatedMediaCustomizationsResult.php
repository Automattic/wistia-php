<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Result\Result;
use Automattic\Wistia\Customizations\ShowRelatedMediaCustomizationsData;

final readonly class UpdateRelatedMediaCustomizationsResult implements Result
{
    public function __construct(#[\SensitiveParameter] public ShowRelatedMediaCustomizationsData $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException("Unexpected endpoint status."); }
        return new self(ShowRelatedMediaCustomizationsData::fromWire($value), $status);
    }
    public function __debugInfo(): array { return []; }
}
