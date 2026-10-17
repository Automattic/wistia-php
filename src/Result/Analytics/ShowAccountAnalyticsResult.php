<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Analytics;

use Automattic\Wistia\Analytics\AccountAnalytics;
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class ShowAccountAnalyticsResult implements Result
{
    public function __construct(#[\SensitiveParameter] public AccountAnalytics $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(AccountAnalytics::fromWire($value), $status);
    }
    public function __debugInfo(): array { return []; }
}
