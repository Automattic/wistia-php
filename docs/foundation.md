# Shared client foundation (P02)

The [root README](../README.md) owns setup, migration status and test commands. [ADR 0001](adr/0001-typed-api-contracts.md) records the approved architecture; [the inventory](rewrite/operations.json) owns endpoint wire contracts. This page describes the shared primitives. The [pagination and polling guide](pagination.md) and [Data reference](reference/data.md#get_background-job-status-backgroundjobstatusid) describe P03's implemented helpers and job-status endpoint. P04's [OAuth guide](oauth.md) describes authorization, code exchange and explicit refresh. P05's [webhook guide](webhooks.md) describes original-byte verification and typed inbound parsing.

## Execution and injection

`Client(Configuration, ?Http\Transport)` defaults to `Http\GuzzleTransport`; that adapter optionally accepts a `GuzzleHttp\ClientInterface`. Constructing a client performs no HTTP. Both timeout fields require finite seconds from `0.001` through `2147483.647` inclusive (30 total, 10 connect by default), rejected at configuration construction otherwise. This range converts to nonzero milliseconds within signed 32-bit cURL capacity; no submillisecond value is rounded into zero/infinite timeout. TLS verification is enabled, and automatic redirects, HTTP-status exceptions, debug output and cookies are disabled. The library installs no retry middleware and sends each execution exactly once. The default requires the package-level `ext-curl` dependency (also required when injecting a transport). It uses a synchronous Guzzle handler with a fresh, nonreused HTTP/1.1 connection per execution: no HTTP/2/3 stream recovery, environment proxy, multi-pass authentication or Expect/100-continue negotiation. Bearer headers are preserved. Bodies of every size use a nonrewindable stream with their known Content-Length, rather than replayable POSTFIELDS buffers; libcurl cannot rewind them. Guzzle factory recovery is given a rejecting callback, never another dispatch. This sacrifices connection pooling and environment-proxy discovery to prevent hidden replay while retaining native finite connect/total timeouts and verified TLS. Applications requiring a proxy must supply a trusted transport/client that independently preserves these safety contracts. An injected transport/client/handler is trusted application code: **it must not add retries, redirects or credential-bearing logging**. Arbitrary injected middleware cannot be made safe by request options alone.

`Client::execute(Http\Request, Http\ResponseDecoder): Http\Response` is a service-building primitive, not an endpoint method or automatic API catalog. A request declares `ApiFamily`, `HttpMethod`, relative-to-family absolute path, `RequestSafety`, explicit allowed 2xx statuses, optional `Encoding\Query` and `Encoding\Body`. Hosts are fixed by family, not supplied by request input. Data/Stats/Analytics share the modern prefix/version; Upload uses `https://upload.wistia.com`; OAuth uses `https://api.wistia.com/oauth` without Bearer injection. P04's [OAuth helpers](oauth.md) implement authorization-URL generation and the separate credential-bearing token-query contracts; they reuse this execution boundary without storing or automatically refreshing tokens. Upload-v2/localization host discrepancies remain P16-owned and unresolved.

Request paths cannot change hosts, include inline query/fragment, or traverse directories. Encode each identifier with `Encoding\Path::segment()` before insertion; never encode a whole path or pass pre-encoded identifiers. There is no global `.json` suffix or key rewriting. Captions' eventual text extensions and endpoint-specific query restrictions remain endpoint responsibilities; the executor invents no format-selecting Accept header.

All non-GET requests are conservatively state-changing. A GET that can create a share link must explicitly use `RequestSafety::MayChangeRemoteState`; services must not infer safety from its verb. There is no retry/replay helper. Timeouts/disconnects after state-changing attempts expose uncertain remote outcome. Successful-status decoding failures and potentially ambiguous 3xx/5xx writes are also marked uncertain. Read-only transport failures do not claim a mutation occurred. Consumer reconciliation is separate from replay; stopping local work cannot undo a remote operation.

## Encoding

`Query` takes an ordered list of exact-name `[name, scalarValue]` pairs. For example:

```php
$query = new Automattic\Wistia\Encoding\Query([
    ['folder_id', 0],
    ['tags[]', 'one'], ['tags[]', 'two'],
    ['cursor[enabled]', 1], ['cursor[after]', 'opaque cursor'],
    ['enabled', false],
]);
```

Repeated bracketed lists and deepObject names remain explicit. Endpoint DTOs select the documented names/styles; the primitive never guesses nested-array conventions. Scalars use RFC3986 percent encoding, booleans encode as `true`/`false`, and integer zero remains `0`. Where a schema needs 0/1, pass integers. No pair means omission; an explicit null pair encodes as `name=` (distinct from omission, not a universal literal-null wire contract). If an endpoint needs a different representation, its DTO must select it explicitly. Signed URLs are raw values encoded once, not pre-encoded inputs.

`Body::json()` preserves exact keys, nested object/list distinctions, false/zero and explicit JSON null. Absence is represented by leaving a key out. `Body::form(Query)` uses the same exact-name encoding with a form content type. JSON encoding failures are local sanitized validation exceptions before any request. Multipart/file workflow encoding and local file validation remain upload-ticket work; no upload implementation is claimed by these JSON/form primitives.

## Results and failures

Each endpoint decoder must implement `ResponseDecoder::decode(TransportResponse): Result\Result`, validate its documented shape and return its concrete DTO. `JsonDecoder`/`JsonResult` validate JSON **syntax/content type only**, preserving objects, lists and scalar/null values; they are not endpoint schema validators. `TextDecoder` requires a declared content type; `EmptyDecoder` requires an empty body. Status must first match the request's declared success statuses. Unexpected 2xx, malformed JSON, wrong content types and endpoint decoder failures become `DecodeException`, not empty success values. There is no universal Media or JSON envelope assumption.

The executor returns `Http\Response` containing the decoded `result`, `status`, and validated `rateLimit` information. The same information is available on failures where supplied. Only numeric `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `X-RateLimit-Reset` and numeric or strict HTTP-date `Retry-After` are retained. The three integer fields accept only nonnegative decimal headers of at most ten raw digits (including leading zeros) whose value fits `PHP_INT_MAX`; unrepresentable or invalid values are discarded as null before casting. Zero and leading zeros remain valid within that raw-length cap. `Retry-After` remains a validated string/date, not a native integer. Arbitrary headers and error-body metadata are not exposed as exception context.

All remote failures extend `Exception\WistiaException`:

| Failure | Type |
| --- | --- |
| 401 | `AuthenticationException` |
| 403 | `PermissionException` |
| 429 | `RateLimitException` |
| Other non-2xx (including redirects) | `ApiException` |
| Transport failure | `TransportException` |
| Undeclared success status/content/decoder failure | `DecodeException` |

Each exposes `status` (null without a response), `rateLimit`, and `remoteOutcomeUncertain`. Messages are fixed, not interpolated from URLs, bodies, tokens, OAuth secrets/grants or signed source URLs. Underlying transport/JSON/decoder exceptions are discarded rather than chained: nested causes may contain secrets. PHP sensitive-parameter annotations protect library-owned trace arguments, and redacted debug views protect ordinary library-object debug output. Exception messages, public metadata and causes do not retain raw sensitive inputs. PHP also captures caller frames: arbitrary application/framework arguments (including PHPUnit provider data) are not library-sanitized context. Do not recursively dump an entire raw `getTrace()` object graph or assume caller data is redacted; applications must protect their own sensitive parameters and logging boundaries. The library does not log requests; applications and injected middleware remain responsible for not logging credentials or raw request/response data.

Status-based failures deliberately tolerate documented differing envelopes: Upload string `error`, object `error.code/detail`, flattened `code/detail`, and HTML 401 bodies. Error bodies are not coerced into a universal error schema or returned as successful Media values. Fixed messages sacrifice untrusted server detail to avoid disclosure.

## Evidence boundary

The original P02 offline PHPUnit suite covers shared configuration, encoding, status/content primitives, sanitized failures, rate information, family policy, safe Guzzle options, redirects and single-attempt ambiguous writes using synthetic fixtures. The [accepted P02 baseline](https://github.com/Automattic/wistia-php/pull/31) passed on PHP 8.4.26 and 8.5.10 with Guzzle 8.2.0 / PHPUnit 12.5.39 under OS-enforced network denial: 107 tests, 800 assertions per run, with caller `zend.exception_ignore_args` set to both 0 and 1. Current default discovery also includes P03's offline pagination/polling and job-status contracts and P04's OAuth and P05's webhook contracts; see the [README](../README.md#tests-and-safety) for current suite evidence. The offline bootstrap explicitly enables argument capture before exceptions; the isolated redaction child also explicitly enables it. Default memory remains 128M. Handler-level cases exercise the actual Guzzle factory finish path after a partially consumed POST and errno65/no response, errno0 recovery and empty writes. Isolated native-option instrumentation delegates to ext-curl without dispatch, checking streamed read/rewind behavior, small/large payload bytes, connection/protocol/negotiation controls and effective native timeout/TLS settings. Redaction checks inspect bounded library-owned trace arguments, normal exception strings, metadata and causes with exception arguments enabled. An isolated PHP subprocess additionally checks complete traces for nine adversarial synthetic failure cases without PHPUnit's caller/provider graph. These checks do not implement or accept any catalog endpoint. Focused diagnostics and syntax checks are not substitutes for PHPUnit. No live account, uploads, OAuth exchange or webhook processing is claimed; independent review and accepted integration are separate gates.
