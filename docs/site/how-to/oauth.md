# Connect an application with OAuth

Use `OAuthService` to build an authorization URL, exchange a validated callback code and refresh a stored grant. Your application owns the browser flow, callback route, correlation checks and token storage.

Wistia may need to enable OAuth for the account. Register the application and redirect URI first; only account owners/managers can authorize an application. Helpers do not provision that access.

## Send the user to authorization

With a [configured client](../tutorials/first-api-call.md#create-the-client):

```php
use Automattic\Wistia\OAuthService;
use Automattic\Wistia\Request\OAuth\AuthorizationRequest;

$oauth = new OAuthService($client);
$redirectUri = 'https://your-app.example/oauth/callback';
$authorizationUrl = $oauth->authorizationUrl(new AuthorizationRequest(
    clientId: $clientId,
    redirectUri: $redirectUri,
));
// Redirect through your application's response layer.
```

Load `$clientId` from your application configuration. Constructing the URL sends no HTTP.

Pass the complete raw registered redirect URI, not a pre-encoded URI. Establish session-bound correlation before redirecting and validate it on callback using the supported callback-URI mechanism. The helper exposes no separate `state`, PKCE or scope-query parameters; it is not a complete OAuth security/session implementation.

## Validate the callback, then exchange the code

Reject denial/error callbacks and absent/invalid codes. Check correlation, session authorization and the expected callback URI **before** this step. Do not trust callback descriptions for logging or UI.

After your application has validated `$callbackCode`, this performs a remote token exchange:

```php
use Automattic\Wistia\Request\OAuth\CodeExchangeRequest;

$tokens = $oauth->exchangeCode(new CodeExchangeRequest(
    clientId: $clientId,
    clientSecret: $clientSecret,
    code: $callbackCode,
    redirectUri: $redirectUri,
));
// Persist only the needed token fields through your application's secret store.
```

The result exposes `accessToken`, `tokenType`, `refreshToken`, `expiresIn` and a list of `scope` strings. `expiresIn` is the returned seconds, not an absolute expiry timestamp or a guarantee of validity. Compute and store any application expiry scheduling deliberately.

## Refresh explicitly

Use the refresh token from your protected store:

```php
use Automattic\Wistia\Request\OAuth\RefreshTokenRequest;

$renewed = $oauth->refresh(new RefreshTokenRequest(
    clientId: $clientId,
    clientSecret: $clientSecret,
    refreshToken: $storedRefreshToken,
));
// Replace the stored fields with the returned grant deliberately.
```

Nothing refreshes or persists automatically. Permissions are configured on the Wistia application; changing them does not update existing grants without reauthorization.

## Protect credential-bearing requests

Wistia's documented token contract puts secrets in the URL query. Never log token-request URLs, raw callbacks, token results or full traces. Default execution does not follow redirects or retry; injected middleware must preserve those protections.

A failed exchange may have consumed a code. If the outcome is uncertain, reconcile or reauthorize rather than blindly replaying it. Rejected/expired grants commonly use `ApiException`, not a special automatically refreshed grant type. See [failure handling](errors.md) and [OAuthService](../../../src/OAuthService.php).
