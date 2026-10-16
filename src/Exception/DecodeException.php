<?php
declare(strict_types=1);

namespace Automattic\Wistia\Exception;

final class DecodeException extends WistiaException
{
    protected static function description(): string { return 'The response did not match the declared status, content type or result contract.'; }
}
