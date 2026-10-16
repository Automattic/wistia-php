<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Channels\Channel;
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class ListChannelsResult implements Result
{
    /** @param list<Channel> $data */
    public function __construct(#[\SensitiveParameter] public array $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(Wire::items($value, Channel::fromWire(...)), $status);
    }
    public function __debugInfo(): array { return []; }
}
