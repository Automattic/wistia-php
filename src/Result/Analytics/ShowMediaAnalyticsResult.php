<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Analytics;

use Automattic\Wistia\Analytics\MediaAnalytics;
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class ShowMediaAnalyticsResult implements Result
{
    public function __construct(#[\SensitiveParameter] public MediaAnalytics $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(MediaAnalytics::fromWire($value), $status);
    }
    public function __debugInfo(): array { return []; }
}
