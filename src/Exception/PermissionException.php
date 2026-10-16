<?php
declare(strict_types=1);

namespace Automattic\Wistia\Exception;

final class PermissionException extends WistiaException
{
    protected static function description(): string { return 'Wistia denied permission for the request.'; }
}
