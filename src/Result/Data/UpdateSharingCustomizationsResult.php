<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Result\Result;
use Automattic\Wistia\Customizations\ShowSharingCustomizationsData;

final readonly class UpdateSharingCustomizationsResult implements Result
{
    public function __construct(#[\SensitiveParameter] public ShowSharingCustomizationsData $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException("Unexpected endpoint status."); }
        return new self(ShowSharingCustomizationsData::fromWire($value), $status);
    }
    public function __debugInfo(): array { return []; }
}
