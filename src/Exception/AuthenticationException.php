<?php
declare(strict_types=1);

namespace Automattic\Wistia\Exception;

final class AuthenticationException extends WistiaException
{
    protected static function description(): string { return 'Wistia authentication failed.'; }
}
