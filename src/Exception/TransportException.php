<?php
declare(strict_types=1);

namespace Automattic\Wistia\Exception;

final class TransportException extends WistiaException
{
    protected static function description(): string { return 'HTTP transfer failed; a state-changing request may have reached Wistia.'; }
}
