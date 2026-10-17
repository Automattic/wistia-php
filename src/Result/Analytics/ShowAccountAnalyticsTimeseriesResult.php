<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Analytics;

use Automattic\Wistia\Analytics\AccountAnalyticsBucket;
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class ShowAccountAnalyticsTimeseriesResult implements Result
{
    /** @param list<AccountAnalyticsBucket> $data */
    public function __construct(#[\SensitiveParameter] public array $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(Wire::items($value, AccountAnalyticsBucket::fromWire(...)), $status);
    }
    public function __debugInfo(): array { return []; }
}
