# Exceptions and rate-limit metadata

Remote failures extend [WistiaException](../../../src/Exception/WistiaException.php). Required fields that fail local validation generally raise `InvalidArgumentException` before HTTP.

| Failure | Exception under `Automattic\Wistia\Exception` |
| --- | --- |
| HTTP 401 | `AuthenticationException` |
| HTTP 403 | `PermissionException` |
| HTTP 429 | `RateLimitException` |
| Other rejected HTTP status, including redirects | `ApiException` |
| Transport failure | `TransportException` |
| Unexpected successful status, content type or response shape | `DecodeException` |

## Common properties

| Property | Type | Meaning |
| --- | --- | --- |
| `status` | `?int` | HTTP status, or null when unavailable |
| `rateLimit` | `?Http\RateLimitInfo` | Validated rate information, if available |
| `remoteOutcomeUncertain` | `bool` | A state-changing request may have taken effect despite failure |

Messages are fixed and sanitized. Raw error documents and underlying exceptions are not retained as chained causes because they can expose credentials or content. Do not assume an exception exposes a provider-specific reason: caption conflicts, for example, expose status 409 without a machine-readable distinction between stale and diverged versions.

## Rate-limit fields

`RateLimitInfo` exposes `limit`, `remaining`, `reset` and `retryAfter`. Invalid headers are discarded rather than blindly cast. `retryAfter` is a validated string (numeric seconds or an HTTP-date), not necessarily an integer.

Headers are hints for application scheduling. The library does not sleep, retry a request or interpret rate metadata as permission to replay a write.

## Local workflow failures

- Invalid request values: `InvalidArgumentException`.
- A traversal beyond `maxPages`: `OverflowException`, not a silently truncated successful export.
- Non-advancing/cyclic cursors: traversal fails rather than loops.
- Webhook signature rejection: `Webhook\VerificationException`.
- Verified but malformed webhook envelope: `Webhook\ParsingException`.

Webhook failures use the shared exception base with no remote HTTP status. Your application decides its HTTP acknowledgement or rejection response.

Read [handle failures safely](../how-to/errors.md) for a practical catch pattern and recovery decisions.
