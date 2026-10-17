# Verify and handle webhooks

Use `WebhookParser` to verify the received signature **before** decoding a delivery. It does not use `Client` or make outbound requests.

You need the webhook secret configured in Wistia, an HTTP route that can read the original bytes, and the received `X-Wistia-Signature` header. The webhook secret is separate from your API token.

## Read the original request

Keep the bytes unchanged: do not decode/re-encode JSON, normalize whitespace or change line endings before verification. Enforce request-size limits at the server/ingress before PHP buffers the body, and reject ambiguous duplicate signature headers there or in your framework.

In a plain-PHP route, the core verification step is:

```php
use Automattic\Wistia\WebhookParser;
use Automattic\Wistia\WebhookVerifier;
use Automattic\Wistia\Webhook\ParsingException;
use Automattic\Wistia\Webhook\VerificationException;

$secret = getenv('WISTIA_WEBHOOK_SECRET');
if ($secret === false || $secret === '') {
    throw new RuntimeException('Configure the separate webhook secret.');
}
$body = file_get_contents('php://input');
if ($body === false) {
    throw new RuntimeException('Cannot read the request body.');
}
$signature = $_SERVER['HTTP_X_WISTIA_SIGNATURE'] ?? null;
$parser = new WebhookParser(new WebhookVerifier($secret));

try {
    $delivery = $parser->parse($body, $signature);
    // Durably hand off verified events before acknowledging successful receipt.
} catch (VerificationException) {
    http_response_code(401);
    exit;
} catch (ParsingException) {
    http_response_code(400);
    exit;
}
```

Load your application's autoloader before this fragment. The shown rejection statuses are application policy, not statuses selected by the library. Add durable processing and acknowledgement behavior before deploying a complete endpoint.

Do not calculate a fresh signature for an incoming body and then use it as the received header: that would not verify its sender. Self-generated signatures belong only in local fixtures.

## Handle the batch

After successful parsing:

- `$delivery->hookUuid` identifies the hook message.
- `$delivery->events` is the ordered event list.
- Each event exposes `uuid`, `type`, `generatedAt` and `payload()`.

Unknown event types and payload fields are retained. Payloads are not all media objects; use the event type and application validation before consuming them. Timestamp strings are preserved, not converted or freshness-checked.

## Make processing durable and idempotent

A correct HMAC proves integrity/authenticity under the secret, not freshness or uniqueness. Deliveries can be duplicated, batched and out of order.

Persist or enqueue verified events durably, deduplicate by event UUID, choose ordering/concurrency rules and acknowledge only according to your delivery guarantees. The library provides no queue, deduplication store, replay policy or event dispatcher. Acknowledging and then discarding events can lose work.

## Know the signature and parsing limits

The signature is exactly a 64-character lowercase hex SHA-256 HMAC of the original body. Prefixes, uppercase, surrounding whitespace and base64 are rejected. Missing/malformed/wrong signatures fail before JSON parsing.

A verified envelope needs a hook UUID and an events array; each event needs UUID, type, generated timestamp string and a payload field. An empty batch is valid.

Bodies, signatures, metadata and payloads can contain sensitive information. Do not log them or arbitrary caller traces. See the [parser reference](../../../src/WebhookParser.php) and the [Laravel](../integrations/laravel.md)/[Symfony](../integrations/symfony.md) integrations for framework extraction examples.
