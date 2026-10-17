<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Analytics;

use Automattic\Wistia\Analytics\WebinarRegistrationBucket;
use Automattic\Wistia\Analytics\RecordList;
use Automattic\Wistia\Result\Result;

final readonly class ShowWebinarRegistrationTimeseriesResult implements Result
{
    public function __construct(#[\SensitiveParameter] /** @var list<WebinarRegistrationBucket> */ public array $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(RecordList::decode($value, WebinarRegistrationBucket::fromWire(...)), $status);
    }
    public function __debugInfo(): array { return []; }
}
