<?php
declare(strict_types=1);

namespace Automattic\Wistia\Http;

use Automattic\Wistia\Result\Result;

/** Endpoint decoders must validate their exact shape and return a concrete typed result. */
interface ResponseDecoder
{
    public function decode(#[\SensitiveParameter] TransportResponse $response): Result;
}
