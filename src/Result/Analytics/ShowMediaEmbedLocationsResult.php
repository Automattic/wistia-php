<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Analytics;

use Automattic\Wistia\Analytics\MediaEmbedLocation;
use Automattic\Wistia\Analytics\RecordList;
use Automattic\Wistia\Result\Result;

final readonly class ShowMediaEmbedLocationsResult implements Result
{
    public function __construct(/** @var list<MediaEmbedLocation> */ #[\SensitiveParameter] public array $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(RecordList::decode($value, MediaEmbedLocation::fromWire(...)), $status);
    }
    public function __debugInfo(): array { return []; }
}
