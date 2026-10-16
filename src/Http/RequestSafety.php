<?php
declare(strict_types=1);

namespace Automattic\Wistia\Http;

// A GET may create a share link; verb alone is not a sufficient safety declaration.
enum RequestSafety
{
    case ReadOnly;
    case MayChangeRemoteState;
}
