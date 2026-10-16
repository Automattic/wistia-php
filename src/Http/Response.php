<?php
declare(strict_types=1);

namespace Automattic\Wistia\Http;

use Automattic\Wistia\Result\Result;

final readonly class Response
{
    public function __construct(
        public Result $result,
        public int $status,
        public RateLimitInfo $rateLimit,
    ) {}

    public function __debugInfo(): array { return ['status' => $this->status]; }
}
