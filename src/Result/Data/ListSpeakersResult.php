<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Accessibility\{ListSpeakersDataItem};
use Automattic\Wistia\Result\Result;

final readonly class ListSpeakersResult implements Result
{
    /** @param list<ListSpeakersDataItem> $data */
    public function __construct(#[\SensitiveParameter] public array $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(Wire::items($value, static fn($v) => ListSpeakersDataItem::fromWire($v)), $status);
    }
    public function __debugInfo(): array { return []; }
}
