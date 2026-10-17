<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Analytics;

use Automattic\Wistia\Analytics\MediaTrafficGroup;
use Automattic\Wistia\Analytics\RecordList;
use Automattic\Wistia\Result\Result;

final readonly class ShowMediaTrafficBreakdownResult implements Result
{
    public function __construct(/** @var list<MediaTrafficGroup> */ #[\SensitiveParameter] public array $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(RecordList::decode($value, MediaTrafficGroup::fromWire(...)), $status);
    }
    public function __debugInfo(): array { return []; }
}
