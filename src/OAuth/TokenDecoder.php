<?php
declare(strict_types=1);

namespace Automattic\Wistia\OAuth;

use Automattic\Wistia\Exception\DecodeException;
use Automattic\Wistia\Http\{JsonDecoder, RateLimitInfo, ResponseDecoder, TransportResponse};
use Automattic\Wistia\Result\OAuth\TokenResult;

/** @internal The OAuth technical reference defines a token object, not a generic JSON result. */
final class TokenDecoder implements ResponseDecoder
{
    public function decode(#[\SensitiveParameter] TransportResponse $response): TokenResult
    {
        try {
            $value = (new JsonDecoder())->decode($response)->value;
            if (!$value instanceof \stdClass
                || !isset($value->access_token, $value->token_type, $value->expires_in, $value->refresh_token, $value->scope)
                || !is_string($value->access_token) || !is_string($value->token_type)
                || !is_int($value->expires_in) || !is_string($value->refresh_token) || !is_array($value->scope)) {
                throw new \UnexpectedValueException('Invalid OAuth token shape.');
            }
            return new TokenResult($value->access_token, $value->token_type, $value->expires_in,
                $value->refresh_token, $value->scope, $response->status, RateLimitInfo::fromResponse($response));
        } catch (\Throwable) {
            throw DecodeException::create($response->status);
        }
    }
}
