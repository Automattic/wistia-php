<?php
declare(strict_types=1);

namespace Automattic\Wistia\Exception;

final class ApiException extends WistiaException
{
    protected static function description(): string { return 'Wistia rejected the request.'; }
}
