<?php
declare(strict_types=1);

namespace Automattic\Wistia\Exception;

use Automattic\Wistia\Http\RateLimitInfo;

abstract class WistiaException extends \RuntimeException
{
    final protected function __construct(
        string $safeMessage,
        public readonly ?int $status = null,
        public readonly ?RateLimitInfo $rateLimit = null,
        public readonly bool $remoteOutcomeUncertain = false,
    ) {
        // No previous exception: HTTP exceptions can contain credentials, URLs and bodies.
        parent::__construct($safeMessage, $status ?? 0);
    }

    public static function create(?int $status = null, ?RateLimitInfo $rateLimit = null, bool $uncertain = false): static
    {
        return new static(static::description(), $status, $rateLimit, $uncertain);
    }

    abstract protected static function description(): string;
}
