<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Result\Result;
use Automattic\Wistia\Customizations\ShowChaptersCustomizationsData;

final readonly class ShowChaptersCustomizationsResult implements Result
{
    public function __construct(#[\SensitiveParameter] public ShowChaptersCustomizationsData $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException("Unexpected endpoint status."); }
        return new self(ShowChaptersCustomizationsData::fromWire($value), $status);
    }
    public function __debugInfo(): array { return []; }
}
