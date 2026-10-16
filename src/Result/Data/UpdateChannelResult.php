<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Channels\Channel;
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class UpdateChannelResult implements Result
{
    public function __construct(#[\SensitiveParameter] public Channel $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(Channel::fromWire($value), $status);
    }
    public function __debugInfo(): array { return []; }
}
