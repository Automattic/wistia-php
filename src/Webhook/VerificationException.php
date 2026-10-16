<?php
declare(strict_types=1);

namespace Automattic\Wistia\Webhook;

use Automattic\Wistia\Exception\WistiaException;

final class VerificationException extends WistiaException
{
    protected static function description(): string
    {
        return 'Webhook signature is missing, malformed or invalid.';
    }
}
