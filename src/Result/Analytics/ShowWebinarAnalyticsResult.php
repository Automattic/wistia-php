<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Analytics;

use Automattic\Wistia\Analytics\WebinarAnalytics;
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class ShowWebinarAnalyticsResult implements Result
{
    public function __construct(#[\SensitiveParameter] public WebinarAnalytics $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(WebinarAnalytics::fromWire($value), $status);
    }
    public function __debugInfo(): array { return []; }
}
