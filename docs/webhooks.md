# Verify and parse webhooks

These framework-independent helpers implement inbound signature verification and typed batch parsing. They do not use `Client`, make HTTP requests, host a route, dispatch events or store delivery state. Applications supply the webhook secret configured in Wistia, not an API Bearer token.

## API and contract

```php
$verifier = new Automattic\Wistia\WebhookVerifier($secret);
$parser = new Automattic\Wistia\WebhookParser($verifier);
$envelope = $parser->parse($originalBodyBytes, $signatureHeaderOrNull);
```

`WebhookVerifier::verify(string $rawBody, ?string $signature): void` throws on failure. `WebhookParser::parse(string $rawBody, ?string $signature): Webhook\Envelope` **always verifies first**, even for invalid JSON. There is no unverified parsing method and no separate mandatory step for callers to remember. The secret must be nonempty; it is used as supplied, without trimming or encoding changes.

The [Wistia webhook contract](https://docs.wistia.com/docs/webhooks.md) specifies `X-Wistia-Signature` as the SHA-256 HMAC **hex digest of the original POST body bytes**, using the configured `secret_key`. Pass one header value, not a list or a whole header line. The accepted representation is exactly 64 lowercase hexadecimal characters, as produced by the documented Ruby `OpenSSL::HMAC.hexdigest` example. Prefixes such as `sha256=`, base64, uppercase, surrounding whitespace and newlines are not accepted. Wistia's illustrative HTTP signature placeholder is not a usable cryptographic test vector. The implementation compares equal-length digests using PHP `hash_equals`; no timing benchmark is claimed.

Do not decode/re-encode JSON, normalize whitespace, change line endings or let a framework transform the body before verification. Obtain the raw bytes once using your framework's original-body facility. A missing signature is `null`; missing, malformed and incorrect signatures all throw `Webhook\VerificationException` before any JSON decoding.

After successful verification, the parser requires:

- A JSON object with a `hook` object containing a nonempty string `uuid`.
- An `events` JSON array. Single events still need an array; batches preserve received order. Empty arrays are accepted because the documented array contract specifies no minimum count.
- Each event must be an object with nonempty string `uuid`, `type` and `generated_at`, and a present `payload` field. IDs are opaque: documentation examples include event IDs that are not RFC UUID strings.

`generatedAt` retains the original `generated_at` string. Wistia describes it as ISO-8601 UTC; the helper validates its string type, not a particular date grammar, precision, timezone spelling, freshness window or ordering interpretation. No date conversion, sorting or replay policy is applied.

The result exposes:

| Value | Access |
| --- | --- |
| Hook message ID | `$envelope->hookUuid` |
| Ordered `list<Webhook\Event>` | `$envelope->events` |
| Event ID, type and original timestamp string | `$event->uuid`, `$event->type`, `$event->generatedAt` |
| Unrestricted event payload | `$event->payload()` |
| Complete event, including metadata/unknown fields | `$event->data()` |
| Complete decoded document, including unknown hook/root fields | `$envelope->data()` |
| Exact original JSON bytes | `$envelope->rawBody()` |

There is no closed event-type registry or guessed payload schema. Unknown types are retained, and payloads are not coerced into a media object: JSON objects remain `stdClass`, arrays remain arrays, and string/boolean/null/numeric values remain native decoded values. As with the shared JSON primitive, integers outside PHP's native range decode as decimal strings (`JSON_BIGINT_AS_STRING`) to avoid rounding. Other JSON numbers use PHP's native numeric representation. Use `rawBody()` when exact number spellings/precision are needed. Decoding uses PHP's depth limit of 512; invalid UTF-8, syntax and over-depth documents fail locally rather than yielding partial data. Decoded objects returned deliberately are mutable application data; readonly DTO properties are not a deep-immutability guarantee.

## Runnable synthetic example

Run from the project root with already provisioned Composer dependencies:

```php
<?php
require 'vendor/autoload.php';

use Automattic\Wistia\WebhookParser;
use Automattic\Wistia\WebhookVerifier;
use Automattic\Wistia\Webhook\ParsingException;
use Automattic\Wistia\Webhook\VerificationException;

// Synthetic values only. A real HTTP integration must read the original body/header.
$secret = 'synthetic-example-secret';
$body = '{"hook":{"uuid":"message-1"},"events":[{"uuid":"event-1","type":"future.example","generated_at":"2024-03-30T15:53:15Z","payload":{"example":true}}]}';
$signature = hash_hmac('sha256', $body, $secret);
$parser = new WebhookParser(new WebhookVerifier($secret));

try {
    $delivery = $parser->parse($body, $signature);
    foreach ($delivery->events as $event) {
        // Synthetic demonstration only; production processing is application-owned.
        assert($event->payload()->example === true);
    }
    echo count($delivery->events), " verified event(s)\n";
} catch (VerificationException) {
    echo "Signature rejected\n";
} catch (ParsingException) {
    echo "Verified body does not match the envelope contract\n";
}
```

Do not generate a signature over an incoming body yourself and then treat it as sender verification: compare the **received** header against the configured secret. The example generates both only to demonstrate the API without network or credentials.

## Failures and consumer responsibilities

Verification and parsing failures extend the shared `Exception\WistiaException`, with fixed local messages, no underlying cause, `status`/`rateLimit` null, and `remoteOutcomeUncertain` false. Bad JSON or missing/wrongly typed required fields throws `Webhook\ParsingException` only after a successful signature check. Empty-secret configuration throws `InvalidArgumentException`. These are local failures, not API errors or instructions for an HTTP response status.

Secrets, raw bodies and signatures are sensitive parameters in library-owned exception frames. Ordinary `print_r`/`var_dump` views of the verifier, parser, envelope and events omit data; serialization is rejected. This is not a general-purpose logging sanitizer: deliberate accessors, public fields, arbitrary application exception frames, reflection or alternative object exporters can expose data. Do not log entire caller traces or returned documents. Successful payloads and metadata can contain names, email addresses and other sensitive information. Applications must set appropriate storage, retention, access-control and logging boundaries, including when using `data()`, `payload()` and `rawBody()`.

A correct HMAC proves authenticity/integrity under the shared secret, **not freshness, uniqueness or permission to replay**. The Wistia guide describes at-least-once, possibly duplicated, out-of-order and batched delivery. Applications own:

- HTTP routing, original-byte/header extraction and rejection of ambiguous duplicate header values.
- Request/body-size limits before buffering, denial-of-service controls, acknowledgements and failure handling.
- Secret provisioning/rotation and safe configuration/storage.
- Processing, durable deduplication using event UUIDs, ordering using suitable event information, concurrency and replay protection. The helper neither drops duplicates nor sorts events.

No event server, dispatcher, replay registry, persistence or browser integration is included. Setup/account availability and delivery from Wistia remain unverified here; no account-plan entitlement is inferred.

## Evidence boundary

The offline tests use synthetic bodies and secrets, including an independent HMAC vector, valid/tampered/missing/malformed signatures, changed raw bytes, verification-before-JSON failures, required envelope types, single/empty/batched events, unknown payload shapes, metadata retention and sanitized error/debug paths. The actual default Composer and strict PHPUnit suites were executed on PHP 8.4.26 and 8.5.10 with OS-enforced network denial. The synthetic example was also executed. These are local contract checks, not proof of live Wistia delivery, framework-specific extraction, application replay protection or deployed routing. No live account calls were made. Independent review, common-document reconciliation and accepted integration are separate lead-owned gates.
