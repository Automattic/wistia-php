<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Analytics;

use Automattic\Wistia\Analytics\WebinarTrafficGroup;
use Automattic\Wistia\Analytics\RecordList;
use Automattic\Wistia\Result\Result;

final readonly class ShowWebinarTrafficBreakdownResult implements Result
{
    public function __construct(#[\SensitiveParameter] /** @var list<WebinarTrafficGroup> */ public array $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(RecordList::decode($value, WebinarTrafficGroup::fromWire(...)), $status);
    }
    public function __debugInfo(): array { return []; }
}
