# Integrate with Symfony

The supplied integration targets **Symfony 8.1.x on PHP 8.4+**. It registers services, a read-only console command and a POST-only webhook-verification route without adding a Symfony dependency to the library.

## Register services

Start with the [service definitions](../../../examples/symfony/config/services.yaml). They inject environment-backed `Configuration`, `Client`, `DataMediaService`, `WebhookVerifier` and `WebhookParser`.

Autowiring supplies the media service to the [command](../../../examples/symfony/src/Command/WistiaListMediaCommand.php). The explicit controller service tag supports request argument resolution for the [webhook controller](../../../examples/symfony/src/Controller/WistiaWebhookController.php).

Merge the definitions into existing applications; do not replace their security, routing or service configuration wholesale.

## Provide environment values

Set `WISTIA_API_TOKEN` and a separate `WISTIA_WEBHOOK_SECRET` using the application's secret/environment mechanism. Symfony owns consumer `.env` loading; the library does not load it.

Protect application caches and deployment artifacts. Clear/warm the consumer cache for its target environment after service changes, and restart long-lived workers when secrets change. Do not export resolved secrets into committed configuration. Keep debug mode off in production.

## Run the read-only command

```sh
php bin/console wistia:media
```

Once the example command is installed and a real token is configured, this sends one GET for up to 25 media and prints only `Media records on this page: N`. Failures return exit 1 with a sanitized class/status; success returns 0. Do not run it as an offline test without an injected transport.

## Configure webhook handling

The [route definition](../../../examples/symfony/config/routes/wistia.yaml) exposes POST `/webhooks/wistia`. The controller reads original bytes and the received signature before parsing.

Invalid signatures return 401; a correctly signed malformed envelope returns 400; the demonstration's post-buffer 1 MiB cap returns 413. Valid envelopes return 200 with `processed: false`.

**Verification acknowledgement is not event processing.** Add durable storage/queueing, UUID-based deduplication, replay controls and your acknowledgement policy before production. Set limits at the HTTP server before body buffering.

Keep this machine-to-machine route stateless as appropriate, while preserving browser-route CSRF/authentication protections. Follow the full [webhook handling guide](../how-to/webhooks.md).

## Try the complete consumer recipe

The [repository Symfony setup](../../../examples/symfony/README.md#create-an-isolated-consumer) provides exact scaffold/dependency commands and test configuration for a fresh isolated application. It preserves the standard kernel, front controller and Composer autoload setup.

With the consumer's development dependencies available:

```sh
vendor/bin/phpunit --configuration phpunit.xml.dist
```

The supplied tests boot the real kernel but replace the Wistia client transport with synthetic responses. They do not establish live Wistia behavior, durable processing or deployed routing.
