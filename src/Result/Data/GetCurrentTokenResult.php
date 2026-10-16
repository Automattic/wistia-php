<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Account\CurrentToken;
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class GetCurrentTokenResult implements Result
{
    public function __construct(#[\SensitiveParameter] public CurrentToken $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(CurrentToken::fromWire($value), $status);
    }
    public function __debugInfo(): array { return []; }
}
