<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Sharing\ResourceURLs;
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class ResolveResourceURLsResult implements Result
{
    public function __construct(#[\SensitiveParameter] public ResourceURLs $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(ResourceURLs::fromWire($value), $status);
    }
    public function __debugInfo(): array { return []; }
}
