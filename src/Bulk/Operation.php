<?php
declare(strict_types=1);

namespace Automattic\Wistia\Bulk;

enum Operation: string
{
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
    case Move = 'move';
}
