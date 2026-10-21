# Laravel integration

This example targets **Laravel 13.x on PHP 8.4+** (with ext-curl and Laravel's required extensions), using the typed Wistia **2.0 API**, not the 1.x client. Laravel 13 allows Guzzle 8; Laravel 12's Guzzle 7 requirement conflicts with this library. No Laravel dependency is added to the library itself.

The example provides a container-bound client and services, a read-only Artisan command, and a webhook endpoint that verifies and acknowledges an envelope **without processing its events**. It does not implement a production webhook pipeline.

## Create an isolated consumer

Run from a reviewed checkout of this repository. The following commands install dependencies into a **new temporary consumer app**, not the library. They require network access for Composer downloads, but do not call Wistia. Use a fresh app for these scaffold copies; merge the shown changes into existing applications rather than overwriting their bootstrap files.

```sh
WISTIA_CHECKOUT="$PWD"
APP=$(mktemp -d /tmp/wistia-laravel.XXXXXX)
composer create-project laravel/laravel "$APP" '^13.0' --no-interaction --no-scripts --prefer-dist
cd "$APP"
composer config repositories.wistia path "$WISTIA_CHECKOUT"
composer require automattic/wistia-php:@dev --no-interaction --no-scripts
composer require --dev 'phpunit/phpunit:^12.5' --with-all-dependencies --no-interaction --no-scripts

mkdir -p app/Console/Commands
cp "$WISTIA_CHECKOUT/examples/laravel/config/wistia.php" config/wistia.php
cp "$WISTIA_CHECKOUT/examples/laravel/app/Providers/WistiaServiceProvider.php" app/Providers/
cp "$WISTIA_CHECKOUT/examples/laravel/app/Console/Commands/WistiaListMedia.php" app/Console/Commands/
cp "$WISTIA_CHECKOUT/examples/laravel/app/Http/Controllers/WistiaWebhookController.php" app/Http/Controllers/
cp "$WISTIA_CHECKOUT/examples/laravel/routes/wistia.php" routes/wistia.php
cp "$WISTIA_CHECKOUT/examples/laravel/bootstrap/app.php" bootstrap/app.php
cp "$WISTIA_CHECKOUT/examples/laravel/bootstrap/providers.php" bootstrap/providers.php
cp "$WISTIA_CHECKOUT/examples/laravel/tests/Feature/WistiaIntegrationTest.php" tests/Feature/
```

The path repository uses your checkout and the root package's unchanged dependency constraints. Do not run Composer from the library root for these steps. Keep the consumer's lock file to reproduce its resolved versions. No database migration or database service is needed for this example. Composer scripts are suppressed so they cannot migrate/create an application database or run setup hooks implicitly.

The supplied [`bootstrap/providers.php`](bootstrap/providers.php) registers the [provider](app/Providers/WistiaServiceProvider.php) beside Laravel's `AppServiceProvider`. It binds `Configuration`, `Client`, `DataMediaService`, `WebhookVerifier` and `WebhookParser` as singletons. The command in `app/Console/Commands` is discovered by Laravel. The supplied [`bootstrap/app.php`](bootstrap/app.php) loads [`routes/wistia.php`](routes/wistia.php) as a separate route file, outside the session-based `web` middleware group. Other web routes retain their CSRF protection; no global CSRF exception is installed. The webhook's authentication is its received HMAC signature, not a browser CSRF token.

## Configuration and read command

For real API use, provide `WISTIA_API_TOKEN` through your consumer application's secret manager/environment and `WISTIA_WEBHOOK_SECRET` separately for incoming webhook verification. Do not commit secrets. Only [`config/wistia.php`](config/wistia.php) calls `env()`; application code reads Laravel configuration. Never use an API token as the webhook secret. Resolution of a service with missing/empty configuration fails locally before sending a request.

For an isolated local scaffold, set `APP_ENV=local`, `APP_DEBUG=false`, `SESSION_DRIVER=array`, `CACHE_STORE=array`, and a consumer-owned `APP_KEY` in your environment. Generate an ephemeral development key with `export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"`; provision and retain an appropriate key through your secret manager for real applications. No root `.env` file is read by the example. Laravel owns any consumer `.env` loading; never copy the library's private `.env` into the consumer app.

```sh
php artisan route:list --path=webhooks/wistia
php artisan wistia:media
```

The command sends **one read-only request** with `per_page: 25` and prints `Media records on this page: N`. It does not print media names, IDs, URLs, bodies, tokens or traces. Typed authentication, permission, rate-limit, transport and decoding failures return exit 1 with a sanitized class/status; success returns 0. No automatic retry occurs. The library applies its finite default request timeouts.

After secrets/configuration change, rebuild `php artisan config:cache` during deployment (or `php artisan config:clear` in development). Once cached, Laravel does not reload `.env`; restarting long-lived workers is application-owned. Cached configuration contains secrets, so protect `bootstrap/cache`, deployment artifacts and access permissions. Never commit the cache. Neither webhook handling nor cached configuration needs an API call to initialize.

## Webhook behavior

POST original body bytes to `/webhooks/wistia` with the **received** `X-Wistia-Signature` header. The [controller](app/Http/Controllers/WistiaWebhookController.php) obtains `Request::getContent()` and the signature before parsing, then uses mandatory library verification-before-decoding. It rejects missing/invalid/ambiguous signatures with **401**, correctly signed malformed envelopes with **400**, and bodies over the demonstration's post-buffer 1 MiB cap with **413**. A valid envelope returns **200**, for example `{"verified_events":1,"processed":false}`. Other methods are not accepted.

This acknowledgement demonstrates verification only: events are **not queued, stored, deduplicated or processed**. Before production, implement durable processing and choose acknowledgement/retry semantics that do not lose events. Applications own routing/TLS, limits at the HTTP server before PHP buffers a body, secret rotation, access controls, UUID-based deduplication, replay/freshness protection, out-of-order/batched events, concurrency, storage and safe logging. HMAC proves integrity/authenticity under the secret, not freshness. Do not log request bodies, signatures, full exceptions or event payloads. Debug mode must remain off in deployed environments.

The command and webhook example never write to Wistia. In any application extension that does mutate Wistia, `remoteOutcomeUncertain` requires reconciling remote state before resubmission: a timeout or decoding failure may follow a successful write. No automatic replay, purchases, media creation or live fixture calls are included. See [webhook responsibilities](https://wistia-php.space.fast/guides/how-to/webhooks.html#make-processing-durable-and-idempotent) and [failure handling](https://wistia-php.space.fast/guides/reference/configuration.html).

## Offline integration checks

From the consumer app directory:

```sh
php artisan config:clear
vendor/bin/phpunit tests/Feature/WistiaIntegrationTest.php
```

The [tests](tests/Feature/WistiaIntegrationTest.php) boot the copied provider/bootstrap, resolve the real services, run the discovered Artisan command against a synthetic client transport, and submit original-byte signed, tampered, missing-signature, wrong-secret, malformed and oversized requests through the documented route. They use synthetic secrets only. A separate check leaves Laravel's test-mode CSRF bypass to verify that ordinary web routes still reject tokenless requests while the webhook route works. No database, real Wistia credentials or Wistia HTTP is required. The tests override the client before requests; never substitute a real token or remove that override.

On macOS, with dependencies already installed, enforce network denial around the same checks:

```sh
sandbox-exec -p '(version 1)(allow default)(deny network*)' php vendor/bin/phpunit tests/Feature/WistiaIntegrationTest.php
```

Verified with real Laravel **13.37.0**, Guzzle **8.2.0** and PHPUnit **12.5.40** on PHP **8.4.26** and **8.5.10**: five integration tests / 30 assertions passed on each runtime under OS-enforced network denial. The library's default offline suites also passed on each runtime (3,266 tests / 28,211 assertions). Configuration caching was checked with synthetic secrets: after `config:cache`, a fresh application resolved the client and webhook parser with the source environment variables removed, without sending requests; the cache was then cleared. The documented route listing and PHP syntax/local-link checks also passed.

Ordinary synthetic checks and OS-enforced denial are distinct evidence; authored tests or lint alone are not proof of framework integration. Live Wistia behavior, remote processing, deployed routing and durable application processing remain outside these checks.

Framework references: [Laravel 13 providers](https://laravel.com/docs/13.x/providers), [configuration caching](https://laravel.com/docs/13.x/configuration#configuration-caching), and [CSRF boundaries](https://laravel.com/docs/13.x/csrf#excluding-uris-from-csrf-protection).
