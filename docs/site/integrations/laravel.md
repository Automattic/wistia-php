# Integrate with Laravel

The supplied integration targets **Laravel 13.x on PHP 8.4+**. It binds the client/services into Laravel's container and provides a read-only Artisan command plus a webhook-verification route.

Laravel 12 requires Guzzle 7, which conflicts with this client's Guzzle 8 requirement. Use a compatible application rather than overriding dependency constraints.

## Register the client and services

Use the supplied [configuration](../../../examples/laravel/config/wistia.php) and [service provider](../../../examples/laravel/app/Providers/WistiaServiceProvider.php) as the starting point.

The provider registers `Configuration`, `Client`, `DataMediaService`, `WebhookVerifier` and `WebhookParser`. Register it alongside your existing application providers; the [example provider list](../../../examples/laravel/bootstrap/providers.php) shows the integration.

Only the configuration file calls Laravel's `env()`. Application code receives services from the container and reads Laravel configuration, so cached configuration works consistently.

## Configure separate secrets

Provide `WISTIA_API_TOKEN` and `WISTIA_WEBHOOK_SECRET` through the consumer app's environment or secret manager. Do not reuse the API token as a webhook secret or copy a repository's private `.env` into an application.

Rebuild `php artisan config:cache` during deployment after configuration changes, or clear it in development. Protect cached configuration because it contains secrets, and restart long-lived workers when needed.

## Run one read

After adding the supplied [command](../../../examples/laravel/app/Console/Commands/WistiaListMedia.php):

```sh
php artisan wistia:media
```

With a real token this makes one read-only request for up to 25 media. It prints `Media records on this page: N`, not metadata or credentials. Typed failures return exit 1 with a sanitized class/status; success returns 0. It does not retry.

## Add the webhook route without weakening browser routes

The [example route](../../../examples/laravel/routes/wistia.php) is loaded separately from the session-based web group. Merge that route/bootstrap change into your application; do not disable CSRF protection globally.

The [controller](../../../examples/laravel/app/Http/Controllers/WistiaWebhookController.php) verifies original bytes and the received signature. Invalid signatures return 401, correctly signed malformed envelopes 400, and the demonstration's post-buffer size limit returns 413.

A valid envelope returns 200 with `processed: false`. **This example acknowledges verification only; it does not queue, store, deduplicate or process events.** Add durable handling and choose acknowledgement semantics before production. Enforce pre-buffer limits at the server and preserve the other [webhook responsibilities](../how-to/webhooks.md#make-processing-durable-and-idempotent).

## Try an isolated consumer

The [repository's Laravel setup](../../../examples/laravel/README.md#create-an-isolated-consumer) gives the exact fresh-app scaffold copies and dependency commands. Use that recipe for a new temporary consumer; merge changes rather than overwriting bootstrap files in an existing app.

The supplied framework tests replace the client with a synthetic transport. Run them from the consumer app after dependencies are available:

```sh
vendor/bin/phpunit tests/Feature/WistiaIntegrationTest.php
```

That is not live Wistia or deployed-webhook verification. Keep debug mode off in production and protect caches, logs and event data.
