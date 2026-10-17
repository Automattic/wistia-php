# Configuration reference

Construct [Configuration](../../../src/Configuration.php) once and pass it to [Client](../../../src/Client.php).

| Constructor argument | Default | Meaning |
| --- | --- | --- |
| `token: ?string` | null | Bearer credential for Data, Stats, Analytics and Upload calls |
| `timeout: float` | 30.0 | Total request timeout, in seconds |
| `connectTimeout: float` | 10.0 | Connection timeout, in seconds |

Both timeout values must be finite, between **0.001 and 2147483.647 seconds**, inclusive. Invalid values fail during configuration construction. A supplied token must be nonempty and contain no whitespace/control bytes. A token is not required to construct the client, but is required before Bearer-authenticated operations.

```php
use Automattic\Wistia\Client;
use Automattic\Wistia\Configuration;

$client = new Client(new Configuration(
    token: $token, // Loaded through your application's secret management.
    timeout: 60.0,
    connectTimeout: 10.0,
));
```

## API version and hosts

`Configuration::API_VERSION` is `2026-09`. Data, Stats and Analytics use that pinned version. Upload and OAuth use their own contracts without this version header. The supported hosts are selected by the operation, not supplied as arbitrary request URLs.

There is no global format switch, token query parameter or automatic `.json` suffix. Caption text formats use their request's `CaptionFormat` enum.

## Transport behavior

The default `GuzzleTransport` verifies TLS, uses finite timeouts and disables automatic retries, redirects, cookies and credential-bearing debug output. It uses fresh HTTP/1.1 connections rather than connection pooling to prevent hidden replay. It does not discover environment proxies.

An optional `Http\Transport` can be injected into `Client`; the Guzzle adapter can also receive a trusted Guzzle client. Custom transports/middleware must preserve the no-retry, no-redirect and credential-safety contracts. Injecting a transport does not remove the package's cURL requirement.

## Secret handling

The library does not load `.env`, persist credentials or refresh tokens automatically. Treat configuration caches and exported application configuration as sensitive. Library debug views are not a substitute for application-owned secret storage.

See [failure handling](../how-to/errors.md) for exceptions and [OAuth](../how-to/oauth.md) for explicit token renewal.
