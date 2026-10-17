<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Analytics;

use Automattic\Wistia\Analytics\AccountEmbedLocation;
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class ShowAccountEmbedLocationsResult implements Result
{
    /** @param list<AccountEmbedLocation> $data */
    public function __construct(#[\SensitiveParameter] public array $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(Wire::items($value, AccountEmbedLocation::fromWire(...)), $status);
    }
    public function __debugInfo(): array { return []; }
}
