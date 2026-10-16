# Pagination and background jobs

These helpers implement bounded remote traversal for PHP applications. P06's [media services](media.md) supply typed list methods and lazy pagination companions for media, tags and deleted media; other resource-list endpoints remain planned. Construct services directly around the [shared Client](foundation.md); no magic dispatch or legacy compatibility is provided.

## Pagination reference

`Pagination\Paginator(strategy, fetch, maxPages: 1000)` accepts a callable taking an `Encoding\Query` and returning a `Pagination\Page`. The callable owns one endpoint read and its resource-specific decoding. `page(position)` fetches one page immediately; omitting the position uses the strategy's starting position. `pages()` and `items()` are lazy generators: constructing them makes no request, and stopping iteration makes no further request. `items()` assigns consecutive keys across pages.

Each strategy takes ordered exact-name filter pairs, a page size and a starting position. Filters (including repeated bracketed lists, false, zero and sorting) remain unchanged on every read. Pagination keys (`page`, `per_page`, scalar/nested `cursor`) must not appear in filters; conflicts fail locally instead of overriding values. Page sizes are locally limited to 1–100. Endpoint services/callers must enforce any narrower limits, supported filters and sort restrictions; these generic adapters do not make an unsupported sort valid.

| Strategy | Wire controls | Continuation |
| --- | --- | --- |
| `OffsetPagination(filters: [], perPage: 100, startPage: 1)` | `per_page`, integer `page` starting at one | Stop on a short/empty page. A full final page requires one subsequent empty read. Integer overflow fails locally. |
| `DataCursorPagination(filters: [], perPage: 100, cursor: null, backwards: false, pageInfo: false)` | Initially `cursor[enabled]=1`; then `cursor[after]`, or `cursor[before]` backwards | `fromRecords()` extracts the last/first record cursor. Stop on a short/empty page; a continuing page needs a nonempty cursor. |
| `AnalyticsCursorPagination(filters: [], perPage: 25, cursor: null)` | Scalar `cursor` (omitted initially), `per_page` | `fromEnvelope()` reads `data` and `page_info`. Follow `has_next_page`/`end_cursor`, even when items are empty; stop when false, even when items are full. |

**Data webinar registrations is a variant of Data cursor pagination**, not another API family. Set `pageInfo: true` and use `DataCursorPagination::fromWebinarRegistrations()` to read `registrations`/`page_info`. Wire controls are scalar `cursor` and `sort_direction=1` (next) or `0` (previous); backwards traversal uses `has_previous_page`/`start_cursor`. Do not supply `sort_direction` in this variant's filters. Other Data endpoints preserve their own supplied sort direction independently of traversal direction.

`Page(items, endCursor, hasNextPage, startCursor, hasPreviousPage)` carries an item list and pagination metadata. Envelope normalization validates list/boolean/cursor shapes, not the item schema. Endpoint decoders must validate and map actual records. Missing/malformed continuation metadata fails rather than silently returning an incomplete list. Cursor values are opaque and encoded once. Repeated or cyclic cursors fail with a fixed message before another read; exceeding `maxPages` raises `OverflowException`, not silent truncation. Items already yielded remain consumed. Fetch/HTTP/decoding failures propagate without retry. The bound is per traversal, not a global application budget.

### Runnable offline example

Run from the repository root with Composer dependencies provisioned:

```php
<?php
require 'vendor/autoload.php';

use Automattic\Wistia\Pagination\{OffsetPagination, Page, Paginator};

$queries = [];
$paginator = new Paginator(
    new OffsetPagination([['sort_by', 'name'], ['tags[]', 'synthetic tag']], perPage: 2),
    function ($query) use (&$queries): Page {
        $queries[] = $query->encode();
        return new Page(count($queries) === 1 ? ['one', 'two'] : ['three']);
    },
    maxPages: 3,
);
$items = iterator_to_array($paginator->items());
if ($items !== ['one', 'two', 'three'] || count($queries) !== 2) {
    throw new RuntimeException('Unexpected traversal.');
}
```

### Exact source contracts and limits

The checked [inventory](rewrite/operations.json) pins first-party Markdown and schemas, not live behavior:

- [List Media](https://docs.wistia.com/reference/get_medias.md): nested record cursors cannot be mixed with offset `page`. Cursor values depend on unchanged sorting. Its narrative permits only `id`/`created` in cursor mode, despite the generated enum omitting `id`; do not allow offset-only `name`/`updated`/`position` in a media cursor endpoint.
- [Stats Events](https://docs.wistia.com/reference/get_stats-events.md) and [Visitors](https://docs.wistia.com/reference/get_stats-visitors.md): page/per-page contracts, capped at 100. Events retain only the last two years. Date-range validation remains endpoint-owned.
- [Webinar registrations](https://docs.wistia.com/reference/get_webinars-webinarid-registrations.md): scalar cursor, registrations envelope, 1–100 results and 0/1 direction.
- [Media conversions](https://docs.wistia.com/reference/get_analytics-medias-mediaid-conversions.md) and [Webinar audience](https://docs.wistia.com/reference/get_analytics-webinars-webinarid-audience.md): scalar cursor, `data`/`page_info`, maximum 100, default 25. Analytics start dates are inclusive/end dates exclusive where documented, unlike Stats inclusive by-date ranges. Media-conversion date windows must not exceed two years. Adapters preserve supplied dates without reinterpreting them.

## Optional finite polling

The [job-status service reference](reference/data.md#get_background-job-status-backgroundjobstatusid) describes P03's status-read endpoint; P06's [media operations](media.md#follow-asynchronous-work) also expose job identifiers for optional polling. It exposes job identifiers and `queued`, `started`, `finished`, `failed` states immediately. Use `Jobs\BackgroundJobPoller(service, runtime: new SystemPollingRuntime())` with `poll(ShowBackgroundJobStatusRequest, PollingOptions)` for optional repeated **status reads**, never replay of the operation that created a job.

`PollingOptions(maxAttempts: 10, intervalSeconds: 1.0)` requires a positive integer attempt count (including the first read) and a finite interval from zero through 86400 seconds. The interval maximum is local policy, not a Wistia requirement or automatic `Retry-After` policy. Attempts are the finite configured bound; there is no additional wall-clock deadline. Each HTTP read retains the client's finite timeouts. The default runtime uses monotonic `hrtime()` and `time_nanosleep()`; tests inject `PollingRuntime::now()`/`wait()` and never sleep.

`PollingResult` returns `last` (the typed endpoint result), `attempts`, `elapsedSeconds`, and `termination`:

- `Completed`: status `finished` with no reported action failures.
- `ReportedFailure`: status `failed`, or any positive bulk failed count/failed action, even if the overall status is `finished` or work is still running. Partial success is preserved in the result, not turned into an unsafe exception message.
- `Exhausted`: the final allowed read is still pending; its status/identifiers remain available. Completion/failure takes precedence on that last read.

There is no wait before the first read or after termination. Read errors propagate immediately without another attempt or wait. Stopping local polling **never cancels the remote job**. A failure also does not mean nothing happened: successful actions can coexist with failures, and remote work may continue. Consumers own reconciliation, scheduling, cancellation policy, storage and safe logging. The library does not initiate purchases, generation, trials or other excluded workflows; reading a documented `purchase` action label is not authorization to submit one.

## Security and evidence boundary

Treat identifiers, cursors, records and per-action error text as sensitive application data. Normal debug views omit payloads, but explicitly accessing DTO fields/serializing them can disclose data. Do not log raw payloads, credential-bearing requests or arbitrary caller traces. Shared failures retain sanitized messages, rate-limit metadata and read-only uncertainty semantics; no automatic retries or redirects are introduced.

Offline synthetic contract tests cover wire encoding, shape validation, filters/order, laziness, empty/final pages, cycles, bounds, all job states, partial failures and fake time/waits. Real default Composer/PHPUnit runs on PHP 8.4 and 8.5 under OS-enforced network denial are recorded in delivery evidence. These checks are not live Wistia verification, fresh-install proof, paid-plan entitlement proof, aggregate endpoint coverage or independent acceptance. P06's media operations have separate offline coverage; other resource endpoints, live credentials and the four P16 Upload contract gaps remain outside these helpers.
