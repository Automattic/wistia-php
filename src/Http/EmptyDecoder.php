<?php
declare(strict_types=1);

namespace Automattic\Wistia\Http;

use Automattic\Wistia\Exception\DecodeException;
use Automattic\Wistia\Result\EmptyResult;

final class EmptyDecoder implements ResponseDecoder
{
    public function decode(#[\SensitiveParameter] TransportResponse $response): EmptyResult
    {
        if ($response->body !== '') { throw DecodeException::create($response->status); }
        return new EmptyResult();
    }
}
