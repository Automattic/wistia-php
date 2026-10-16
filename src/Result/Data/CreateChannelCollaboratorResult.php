<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Channels\ChannelCollaborator;
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class CreateChannelCollaboratorResult implements Result
{
    public function __construct(#[\SensitiveParameter] public ChannelCollaborator $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 201) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(ChannelCollaborator::fromWire($value), $status);
    }
    public function __debugInfo(): array { return []; }
}
