# OAuth helpers

`OAuthService` provides authorization-URL construction, authorization-code exchange and explicit refresh for server-side PHP applications. It is constructed directly around the shared [`Client`](foundation.md); it does not store tokens, open a browser, host callbacks or renew credentials automatically. This is part of the breaking rewrite, not a compatibility layer for the old magic client.

## Prerequisites and contract

Wistia's [OAuth technical reference](https://docs.wistia.com/docs/authenticating-with-oauth2.md) is the source for these helpers. OAuth is not available to all accounts: Wistia enablement may be required. Only account **owners and managers** can authorize an application. Register the application and its redirect URI with Wistia before use; this library does not provision accounts or request enablement.

| Method | Typed input | Behavior/result |
| --- | --- | --- |
| `authorizationUrl()` | `Request\OAuth\AuthorizationRequest(clientId, redirectUri)` | Returns `https://app.wistia.com/oauth/authorize` with `client_id`, `redirect_uri`, `response_type=code`. Performs no HTTP. |
| `exchangeCode()` | `Request\OAuth\CodeExchangeRequest(clientId, clientSecret, code, redirectUri)` | POSTs to `https://api.wistia.com/oauth/token` with documented query keys `client_id`, `client_secret`, `grant_type=authorization_code`, `code`, `redirect_uri`. |
| `refresh()` | `Request\OAuth\RefreshTokenRequest(clientId, clientSecret, refreshToken)` | POSTs to the same token URL with query keys `client_id`, `client_secret`, `grant_type=refresh_token`, `refresh_token`. |

Token requests have no body, injected Bearer header or modern API-version header. Query values use RFC3986 encoding exactly once. In particular, pass the **complete raw redirect URI**, including its existing query and percent escapes, not a pre-encoded whole URI. The configured redirect URI must match the registered URI; Wistia documents adding arbitrary query parameters to that URI for consumer correlation. There is no separate `state`, PKCE, scope-query or other undocumented security parameter in this API. Permissions are configured on the Wistia application; changing them does not update existing token grants without user reauthorization.

All request fields must be nonempty strings; empty fields throw a fixed-message `InvalidArgumentException` locally. Values are not trimmed or rewritten. Registration matching, URI suitability for your application, credential validity and grant expiry remain application/server checks, not inferred local validation.

Both token methods return `Result\OAuth\TokenResult` after a 200 JSON response. Its readonly fields are:

- `accessToken: string`, `tokenType: string`, `refreshToken: string` — nonempty, returned without normalization.
- `expiresIn: int` — nonnegative seconds exactly as returned, including zero. No absolute expiry timestamp or validity promise is computed. Values exceeding the runtime's native integer capacity are rejected, not cast or rounded.
- `scope: list<string>` — exact returned list, preserving order, duplicates and unknown scope names; not a space-delimited string.
- `status: int`, `rateLimit: Http\RateLimitInfo` — shared status and validated rate information.

Missing/null/mistyped fields, malformed JSON, incorrect content types or unexpected success statuses fail decoding. Unknown additional response properties are ignored rather than retained as potentially sensitive metadata. The source illustrates `token_type=bearer`, `expires_in=21600` and `scope=["all:read"]`; these examples are not hard-coded values. The documented refresh token remains the same until user reauthorization; always handle the returned token explicitly rather than relying on hidden library persistence.

## Runnable offline example

This synthetic example can run after Composer dependencies have been provisioned. It makes **no network calls** and uses no real credentials. The anonymous transport is test data, not a production transport.

```php
<?php
require 'vendor/autoload.php';

use Automattic\Wistia\{Client, Configuration, OAuthService};
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Request\OAuth\{AuthorizationRequest, CodeExchangeRequest, RefreshTokenRequest};

$transport = new class implements Transport {
    public function send(#[\SensitiveParameter] TransportRequest $request): TransportResponse
    {
        return new TransportResponse(200, ['Content-Type' => ['application/json']],
            '{"access_token":"synthetic-access","token_type":"bearer","expires_in":21600,"refresh_token":"synthetic-refresh","scope":["all:read"]}');
    }
};
$oauth = new OAuthService(new Client(new Configuration(), $transport));
$redirectUri = 'https://consumer.invalid/callback?correlation=synthetic-nonce';
$authorizationUrl = $oauth->authorizationUrl(new AuthorizationRequest('synthetic-client', $redirectUri));
$tokens = $oauth->exchangeCode(new CodeExchangeRequest(
    'synthetic-client', 'synthetic-secret', 'synthetic-code', $redirectUri,
));
$renewed = $oauth->refresh(new RefreshTokenRequest(
    'synthetic-client', 'synthetic-secret', $tokens->refreshToken,
));
assert($renewed->scope === ['all:read']);
```

For production, omit the synthetic transport to use the default safe HTTP adapter. Redirecting the user and handling the callback happen in **your application**. The documented success callback includes `code`, `account_id`, `account_url`, `account_name`, `contact_id`, `contact_name`, and `contact_email`. Denial returns `error` and `error_description` instead of a code. Validate correlation and callback input before exchanging anything; do not send an absent code or treat denial as a token response. The library does not parse callbacks or trust remote descriptions for logging/UI.

Your application must securely generate/validate correlation, manage sessions and authorization, select a suitable registered callback URI, protect callback data, and choose secure persistence and refresh timing. Persist the particular token fields you need using your own protected storage; no library store or automatic refresh is installed.

## Failures and credential safety

Token calls reuse the [foundation's exception contract](foundation.md#results-and-failures): status-based API/authentication/permission/rate-limit failures, transport failures and decoding failures. Rejected/expired grants returned as HTTP 400 are `ApiException`, not an invented grant-error hierarchy. Supplied validated rate data is accessible on errors and results. Messages are fixed and raw remote error descriptions/bodies and nested causes are discarded.

No automatic retry or credential-bearing redirect is performed. Transport failures, malformed successful exchanges and ambiguous redirect/server failures report `remoteOutcomeUncertain=true`: a grant may have been consumed. Do not blindly replay; your application owns reconciliation/reauthorization. A rejected grant is not silently refreshed.

The documented token URL contains secrets in its **query**. Never log it, a request body, tokens, the authorization URL's correlation data, or raw callback/response data. Trusted injected transports and application middleware must preserve the no-retry/no-redirect/no-credential-logging contract. In-memory token properties are intentionally readable. Request/token `print_r`, `var_dump` and JSON views are redacted; PHP serialization is refused. These views are not protected storage: explicit property access, casting, reflection and `var_export` can expose data. Persist fields deliberately, not by dumping objects. Annotate your own sensitive function parameters and protect caller logs/traces; the library cannot sanitize arbitrary application frames.

## Verification boundary

Offline synthetic tests capture the exact authorization/code/refresh contract, typed token fields, scope/expiry/type boundaries, denial/rejected/expired-grant status handling, malformed successes, sanitized failures, rate data and one-attempt uncertainty. The suite is executed on PHP 8.4 and 8.5 under OS-enforced network denial. No real account authorization, token exchange, refresh, account-plan access or callback security integration has been live-verified. This helper guide does not claim delivery of the remaining endpoint catalog or whole-library acceptance.
