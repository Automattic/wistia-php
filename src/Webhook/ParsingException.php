<?php
declare(strict_types=1);

namespace Automattic\Wistia\Webhook;

use Automattic\Wistia\Exception\WistiaException;

final class ParsingException extends WistiaException
{
    protected static function description(): string
    {
        return 'Verified webhook body must be JSON with hook.uuid and an events array of uuid, type, generated_at and payload.';
    }
}
