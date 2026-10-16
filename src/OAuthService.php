<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Query;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\OAuth\TokenDecoder;
use Automattic\Wistia\Request\OAuth\{AuthorizationRequest, CodeExchangeRequest, RefreshTokenRequest};
use Automattic\Wistia\Result\OAuth\TokenResult;

/** Explicit OAuth helpers; applications own callbacks, correlation and token storage. */
final class OAuthService
{
    public function __construct(#[\SensitiveParameter] private readonly Client $client) {}

    public function authorizationUrl(#[\SensitiveParameter] AuthorizationRequest $request): string
    {
        return 'https://app.wistia.com/oauth/authorize?' . (new Query([
            ['client_id', $request->clientId],
            ['redirect_uri', $request->redirectUri],
            ['response_type', 'code'],
        ]))->encode();
    }

    public function exchangeCode(#[\SensitiveParameter] CodeExchangeRequest $request): TokenResult
    {
        return $this->token(new Query([
            ['client_id', $request->clientId],
            ['client_secret', $request->clientSecret],
            ['grant_type', 'authorization_code'],
            ['code', $request->code],
            ['redirect_uri', $request->redirectUri],
        ]));
    }

    public function refresh(#[\SensitiveParameter] RefreshTokenRequest $request): TokenResult
    {
        return $this->token(new Query([
            ['client_id', $request->clientId],
            ['client_secret', $request->clientSecret],
            ['grant_type', 'refresh_token'],
            ['refresh_token', $request->refreshToken],
        ]));
    }

    private function token(#[\SensitiveParameter] Query $query): TokenResult
    {
        $response = $this->client->execute(new Request(ApiFamily::OAuth, HttpMethod::Post, '/token',
            RequestSafety::MayChangeRemoteState, [200], $query), new TokenDecoder());
        return $response->result;
    }

    public function __debugInfo(): array { return []; }
    public function __serialize(): array { throw new \LogicException('OAuth services cannot be serialized.'); }
}
