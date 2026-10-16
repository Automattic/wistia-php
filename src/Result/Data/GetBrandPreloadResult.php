<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Brands\BrandPreload;
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class GetBrandPreloadResult implements Result
{
    public function __construct(#[\SensitiveParameter] public BrandPreload $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(BrandPreload::fromWire($value), $status);
    }
    public function __debugInfo(): array { return []; }
}
