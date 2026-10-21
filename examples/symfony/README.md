# Symfony integration

This example targets **Symfony 8.1.x on PHP 8.4+**, with ext-curl and Symfony's required extensions. It uses the typed Wistia **2.0 API**, not the 1.x package. Symfony is installed only in a separate consumer application; the library's Composer dependencies remain unchanged.

It supplies container bindings, a read-only console command, and a webhook route that verifies original bytes and acknowledges valid envelopes **without processing events**. Durable production webhook handling is application-owned.

## Create an isolated consumer

Run from a reviewed checkout of this repository. The following commands download dependencies into a **new temporary consumer app**, not the library. They need Composer network access but do not call Wistia. Symfony Flex installs the standard framework recipe and registers FrameworkBundle; no example-specific bundle is needed. Composer scripts are suppressed so application cache/setup commands do not run implicitly.

```sh
WISTIA_CHECKOUT="$PWD"
APP=$(mktemp -d /tmp/wistia-symfony.XXXXXX)
composer create-project symfony/skeleton "$APP" '8.1.*' --no-interaction --no-scripts --prefer-dist
cd "$APP"
composer config repositories.wistia path "$WISTIA_CHECKOUT"
composer require automattic/wistia-php:@dev --no-interaction --no-scripts
composer require --dev 'phpunit/phpunit:^12.5' 'symfony/browser-kit:8.1.*' 'symfony/css-selector:8.1.*' --no-interaction --no-scripts

mkdir -p src/Command src/Controller config/routes config/packages/test tests
cp "$WISTIA_CHECKOUT/examples/symfony/src/Command/WistiaListMediaCommand.php" src/Command/
cp "$WISTIA_CHECKOUT/examples/symfony/src/Controller/WistiaWebhookController.php" src/Controller/
cp "$WISTIA_CHECKOUT/examples/symfony/config/services.yaml" config/services.yaml
cp "$WISTIA_CHECKOUT/examples/symfony/config/services_test.yaml" config/services_test.yaml
cp "$WISTIA_CHECKOUT/examples/symfony/config/routes/wistia.yaml" config/routes/
cp "$WISTIA_CHECKOUT/examples/symfony/config/packages/test/framework.yaml" config/packages/test/framework.yaml
cp "$WISTIA_CHECKOUT/examples/symfony/tests/bootstrap.php" tests/bootstrap.php
cp "$WISTIA_CHECKOUT/examples/symfony/tests/WistiaIntegrationTest.php" tests/
cp "$WISTIA_CHECKOUT/examples/symfony/phpunit.xml.dist" phpunit.xml.dist
```

Use a fresh skeleton for these copies. In an existing consumer, merge service/test definitions rather than overwriting its files. Do not replace the app's security, firewall, routing or CSRF policy globally. The skeleton's `src/Kernel.php`, `config/bundles.php`, `bin/console`, public front controller and Composer autoload configuration remain in place. Keep the consumer lock file to reproduce the resolved framework and dependencies. Do not run these Composer commands from the library root; no database or migration is required.

The [service definitions](config/services.yaml) register `Configuration`, `Client`, `DataMediaService`, `WebhookVerifier` and `WebhookParser`. Autowiring injects the media service into the [command](src/Command/WistiaListMediaCommand.php); `#[AsCommand]` registers `wistia:media`. The explicit controller service tag enables request argument resolution for the [controller](src/Controller/WistiaWebhookController.php). Symfony's standard kernel imports `config/routes/*.yaml`, including the [POST-only route](config/routes/wistia.yaml).

## Configuration and commands

Provide `WISTIA_API_TOKEN` and a **separate** `WISTIA_WEBHOOK_SECRET` via the consumer application's environment/secret manager. The configuration uses Symfony's environment processors and container injection, not ad hoc `getenv()` calls in application code. Never use an API token as the webhook secret or commit credentials. Symfony owns consumer `.env` loading; do not copy the library's private `.env` into the consumer. Missing/invalid environment configuration fails locally during service resolution, before an API request.

For a local consumer, set `APP_ENV=dev`, `APP_DEBUG=0`, `DEFAULT_URI=http://localhost` (or your application's public base URL), and a consumer-owned `APP_SECRET` through your environment. You can generate an ephemeral development secret with `export APP_SECRET="$(php -r 'echo bin2hex(random_bytes(32));')"`; provision and retain an appropriate secret through application secret management for real deployments. Do not enable shell tracing around secrets. Debug mode must remain off in deployed environments.

```sh
php bin/console debug:router wistia_webhook
php bin/console wistia:media
```

The first command identifies POST `/webhooks/wistia`. The media command sends **one read-only GET**, requests at most 25 records, and prints `Media records on this page: N`. It prints no metadata, IDs, URLs or remote bodies. Typed library failures return exit 1 with only a class and HTTP status; success returns 0. Requests have finite default client timeouts and are not automatically retried. The command requires a real API token and remote permission; do not run it as an offline check without the tests' injected transport.

Symfony normally resolves `%env(...)%` values at runtime; do not deliberately resolve secrets into exported configuration or committed cache artifacts. Protect application caches and deployment artifacts. After service/configuration changes, clear/warm the consumer's cache for its target environment (`APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear`), and restart long-lived workers when secrets change. The library does not load configuration or manage worker lifetimes for the application.

## Webhook contract and application responsibilities

Send original request bytes to POST `/webhooks/wistia` with the **received** `X-Wistia-Signature` header. The controller uses `Request::getContent()` and the header before any JSON decoding, then invokes the existing verifier/parser. Missing, malformed, wrong-secret or ambiguous signatures return **401**. A correctly signed malformed envelope returns **400**. A post-buffer demonstration limit returns **413** above 1 MiB. Valid envelopes return **200**, for example `{"verified_events":1,"processed":false}`. Other HTTP methods are rejected.

No form CSRF token is expected on this machine-to-machine endpoint; its authentication is the verified HMAC. The minimal skeleton does not install a session/form security bundle. In a larger application, configure only this route as needed for stateless webhook access and preserve browser-route protections; never disable CSRF or authentication globally. Do not invent a signature for an incoming body: use the sender's received header. The tests' signature generation is **fixture-only**.

This acknowledgement does **not** queue, store, deduplicate or process events. Replace the demonstration hook with durable handling and choose acknowledgement/retry semantics before production. Applications own pre-buffer HTTP-server/body-size limits, TLS, secret rotation, durable event-UUID deduplication, replay/freshness controls, ordering for batched/out-of-order delivery, concurrency, storage/access controls and safe logging. HMAC alone proves neither freshness nor uniqueness. Do not log raw bodies, signatures, payloads or full exception traces. See the [webhook guide](https://wistia-php.space.fast/guides/how-to/webhooks.html#make-processing-durable-and-idempotent).

Neither this command nor controller writes to Wistia. If you add mutating operations, a typed failure with `remoteOutcomeUncertain` means remote state must be reconciled before resubmission: a timeout or decoding failure can follow a successful write. There is no automatic replay, media creation, purchase or live fixture request in this example. See [failure handling](https://wistia-php.space.fast/guides/reference/configuration.html).

## Offline framework integration checks

From the isolated consumer app:

```sh
vendor/bin/phpunit --configuration phpunit.xml.dist
```

The [test bootstrap](tests/bootstrap.php) sets synthetic configuration directly and intentionally does not load any `.env` file. The [test-only service definition](config/services_test.yaml) makes the client replaceable; the production service remains private. [Integration tests](tests/WistiaIntegrationTest.php) boot the real kernel and service/route configuration, discover the actual command, replace only the client with a synthetic transport, and submit signed/tampered/missing/wrong-secret/malformed/oversized webhook requests through Symfony's HTTP kernel. They assert sanitized success/failure output, the bounded single read, POST-only routing and acknowledgement without processing. No Wistia credentials, database or HTTP connection are required; never remove the fake-client override or substitute a real token.

On macOS, enforce network denial after installation:

```sh
sandbox-exec -p '(version 1)(allow default)(deny network*)' php vendor/bin/phpunit --configuration phpunit.xml.dist
```

Verified with real Symfony **8.1.9**, Guzzle **8.2.0** and PHPUnit **12.5.40** on PHP **8.4.26** and **8.5.10**: five integration tests / 35 assertions passed on each runtime under OS-enforced network denial. The library's default offline suites also passed on each runtime (3,266 tests / 28,211 assertions). Symfony YAML lint, production-container lint and route inspection passed under network denial; PHP syntax, PHPUnit XML and local-link checks passed. The expected GET rejection can produce Symfony's logged 405 message during the route test; the suite still asserts the response and passes.

The initial Symfony 8.0 installation was rejected by Composer security advisories. This guide uses 8.1; no advisory checks were disabled or ignored, and the completed 8.1 installation reported no security vulnerability advisories.

Ordinary synthetic tests and OS-enforced network denial are distinct evidence. Authored tests or lint alone are not proof of real framework integration. Live Wistia behavior, deployed routing, plan entitlement and durable application event processing remain outside these checks.

Framework references: [Symfony 8.1 services](https://symfony.com/doc/8.1/service_container.html), [commands](https://symfony.com/doc/8.1/console.html), and [kernel/HTTP integration testing](https://symfony.com/doc/8.1/testing.html).
