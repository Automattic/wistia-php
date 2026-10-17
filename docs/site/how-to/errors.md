# Handle failures safely

Catch typed exceptions at your application's API boundary. Use selected metadata to decide what to do next; avoid logging raw requests, responses, tokens or whole exception traces.

## Catch a library failure

With a [configured client](../tutorials/first-api-call.md#create-the-client):

```php
use Automattic\Wistia\DataMediaService;
use Automattic\Wistia\Exception\WistiaException;
use Automattic\Wistia\Request\Data\ListMediaRequest;

$media = new DataMediaService($client);
try {
    $result = $media->listMedia(new ListMediaRequest(per_page: 25));
} catch (WistiaException $error) {
    $status = $error->status;
    $retryAfter = $error->rateLimit?->retryAfter;
    $needsReconciliation = $error->remoteOutcomeUncertain;
    // Hand these selected values to your application's recovery/logging layer.
}
```

This snippet reads media; it does not change it. Catch specific subclasses when you need different application behavior for authentication, permissions or rate limiting. The [exception reference](../reference/errors.md) lists them.

## Choose the response to the failure

| Situation | Application action |
| --- | --- |
| Local `InvalidArgumentException` | Correct the input or configuration before sending |
| 401 authentication failure | Check credential validity and configuration |
| 403 permission failure | Check token permissions, resource access and feature/plan availability |
| 429 rate limit | Schedule work using available rate information; do not spin in a tight loop |
| Transport or decoding failure on a read | Decide whether a bounded later read is appropriate |
| Uncertain outcome on a write | Reconcile remote state before considering resubmission |

An HTTP error does not grant permission to retry. The library sends each request once and does not follow redirects.

## Treat an uncertain write differently

A timeout may happen after Wistia accepted a change. A successful HTTP status can also be followed by a decoding failure, leaving the write outcome uncertain.

If `remoteOutcomeUncertain` is true, check the resource or job through an appropriate read or recovery process. Do not blindly rerun an upload, invitation, publication, deletion or bulk batch. Resubmission could duplicate an effect that already occurred.

The flag does not roll back remote state or provide idempotency. Your application owns reconciliation and the eventual decision.

## Keep diagnostics useful and safe

Exception messages and library-owned debug views are sanitized. Explicit DTO access and arbitrary application/framework trace arguments can still expose sensitive data. Log only what you need, with appropriate access controls and retention.

Do not expect raw provider-specific error codes in sanitized exceptions. For example, caption-edit 409 responses do not expose whether the version was stale or diverged. Keep recovery bounded and escalate persistent conflicts rather than guessing.
