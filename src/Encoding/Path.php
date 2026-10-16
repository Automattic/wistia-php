<?php
declare(strict_types=1);

namespace Automattic\Wistia\Encoding;

final class Path
{
    public static function segment(#[\SensitiveParameter] string $identifier): string
    {
        if ($identifier === '' || $identifier === '.' || $identifier === '..') {
            throw new \InvalidArgumentException('A nonempty, non-traversal path identifier is required.');
        }
        return rawurlencode($identifier);
    }
}
