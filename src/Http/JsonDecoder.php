<?php
declare(strict_types=1);

namespace Automattic\Wistia\Http;

use Automattic\Wistia\Exception\DecodeException;
use Automattic\Wistia\Result\JsonResult;

final class JsonDecoder implements ResponseDecoder
{
    public function decode(#[\SensitiveParameter] TransportResponse $response): JsonResult
    {
        $type = strtolower(trim(explode(';', $response->header('Content-Type') ?? '')[0]));
        if ($type !== 'application/json' && !preg_match('~^application/[a-z0-9!#$&^_.+-]+\+json$~D', $type)) {
            throw DecodeException::create($response->status);
        }
        try {
            return new JsonResult(json_decode($response->body, false, 512, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING));
        } catch (\Throwable) {
            throw DecodeException::create($response->status);
        }
    }
}
