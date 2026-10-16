<?php
declare(strict_types=1);

namespace Automattic\Wistia\Exception;

final class RateLimitException extends WistiaException
{
    protected static function description(): string { return 'Wistia rate limit exceeded.'; }
}
