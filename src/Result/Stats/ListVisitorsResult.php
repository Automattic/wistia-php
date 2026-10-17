<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Stats;

use Automattic\Wistia\Stats\Visitor;
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class ListVisitorsResult implements Result
{
    /** @param list<Visitor> $data */
    public function __construct(#[\SensitiveParameter] public array $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(Wire::items($value, Visitor::fromWire(...)), $status);
    }
    public function __debugInfo(): array { return []; }
}
