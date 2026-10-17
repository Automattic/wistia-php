# Account Analytics reference

Five account Analytics operations are implemented for [#26](https://github.com/Automattic/wistia-php/issues/26). Media and webinar Analytics remain separate planned work. These are offline-tested contracts, not live-verified account access.

## Usage

Construct `AnalyticsAccountService` around a configured `Client`. Requests live in `Automattic\Wistia\Request\Analytics`; corresponding results live in `Automattic\Wistia\Result\Analytics`. Results expose `status` and typed `data`, except media lookup, which exposes `media_hashed_ids` directly. No magic dispatch is involved.

```php
use Automattic\Wistia\AnalyticsAccountService;
use Automattic\Wistia\Request\Analytics\ShowAccountTopContentRequest;

// $client is configured as described in the root README.
$analytics = new AnalyticsAccountService($client);
$result = $analytics->showAccountTopContent(new ShowAccountTopContentRequest(
    start_date: '2026-01-01',
    end_date: '2026-02-01',
    group_by: 'media',
    hashed_ids: ['media-one', 'media-two'],
    sort_by: 'plays',
    sort_direction: 'desc',
    per_page: 10,
));
foreach ($result->data as $media) {
    echo $media->media_hashed_id, ': ', $media->plays, PHP_EOL;
}
```

All operations send GET to `https://api.wistia.com/modern/analytics/account` (with the suffix below), Bearer authentication, and pinned `X-Wistia-API-Version: 2026-09`. They require **Read detailed stats**, or delegated contact authorization with suitable permissions; an expiring delegated token needs an account authorization granting `view-stats`. No plan entitlement or live availability is asserted. Remote permission/feature restrictions remain observable failures.

## Dates and results

Dates use strict `YYYY-MM-DD` calendar values. **Start is inclusive; end is exclusive**, unlike Stats by-date ranges. When both dates are supplied, end must follow start. The first four operations require both dates and allow at most two calendar years. A leap-day start's two-year anniversary is clamped to February's final day. This conservative local boundary is library validation, not a live-tested server interpretation.

Response fields retain their wire names. Nullable metrics keep `null`, not zero. Optional missing properties map to `null` with `presentFields` recording whether each property was supplied, so omission and explicit null remain distinguishable. Zero counts/rates are preserved. Unknown properties, wrong shapes/types and non-finite numeric values fail decoding. Debug output hides records; explicitly reading properties remains application-owned sensitive data handling.

These operations return aggregate objects, bare arrays or an ID object, **not cursor envelopes**. Their `per_page` is a result limit; there is no documented next cursor/page or traversal helper. Time values and metric types follow each endpoint schema: account and timeseries `played_time` are integers, while ranking and embed-location `played_time` allow fractional numbers. The retained source schemas specify `played_time` in seconds; the library preserves those values without conversion to Stats' `hours_watched` units.

## get_analytics-account

[`showAccountAnalytics(ShowAccountAnalyticsRequest): ShowAccountAnalyticsResult`](https://docs.wistia.com/reference/get_analytics-account.md)

No suffix. Required `start_date`, `end_date`. `data` is `Analytics\AccountAnalytics` with nullable `unique_loads`, `unique_plays`, `plays`, `play_rate`, `played_time`, `unique_visitors`, `engagement_rate`, `form_conversions`.

## get_analytics-account-timeseries

[`showAccountAnalyticsTimeseries(ShowAccountAnalyticsTimeseriesRequest): ShowAccountAnalyticsTimeseriesResult`](https://docs.wistia.com/reference/get_analytics-account-timeseries.md)

Suffix `/timeseries`. Required dates and `granularity`: `daily`, `weekly` or `monthly`. Returns `list<Analytics\AccountAnalyticsBucket>` in `data`, with a string `timestamp` and the aggregate metrics above. No local bucket-filling or timezone conversion is performed.

## get_analytics-account-top-content

[`showAccountTopContent(ShowAccountTopContentRequest): ShowAccountTopContentResult`](https://docs.wistia.com/reference/get_analytics-account-top-content.md)

Suffix `/top_content`. Required dates. Optional inputs are omitted when null, preserving server defaults:

| Input | Contract |
| --- | --- |
| `group_by` | `media` (default), `channel`, `project`; `project` means folder, not the Data wire name `folder`. |
| `hashed_ids` | Up to 1000 strings, only with omitted/`media` grouping; emits repeated `hashed_ids[]` keys, not CSV or numeric bracket keys. |
| `sort_by` | `plays` (default), `loads`, `play_rate`, `engagement_rate`, `played_time`, `unique_visitors`. |
| `sort_direction` | `asc` or `desc` (default). |
| `per_page` | 1–1000; server default is the requested ID count, or 10 without IDs. |

`data` is `list<Analytics\RankedMedia|Analytics\RankedChannel|Analytics\RankedProject>`. Media records expose `media_hashed_id`, name/type/duration/thumbnail/uploaded/deleted fields; channel records expose `channel_hashed_id`, title/episode-count/created/URL; project records expose nullable `project_hashed_id`, name/created/URL/video-count. Each carries typed nullable metrics. Record selection uses its group-specific hashed-ID key; malformed or mixed variants fail decoding.

## get_analytics-account-embed-locations

[`showAccountEmbedLocations(ShowAccountEmbedLocationsRequest): ShowAccountEmbedLocationsResult`](https://docs.wistia.com/reference/get_analytics-account-embed-locations.md)

Suffix `/embed_locations`. Required dates. Optional `sort_by` and `sort_direction` use the ranking enums/defaults; `per_page` is 1–100 (default 10). `data` is `list<Analytics\AccountEmbedLocation>` with `embed_domain`, `media_count`, `loads`, `plays`, `play_rate`, `played_time`, `engagement_rate`, `unique_visitors`. These are domain aggregates, not individual page URLs.

## get_analytics-account-media-by-embed-location

[`findMediaByEmbedLocation(FindMediaByEmbedLocationRequest): FindMediaByEmbedLocationResult`](https://docs.wistia.com/reference/get_analytics-account-media-by-embed-location.md)

Suffix `/media_by_embed_location`. Required `embed_url` accepts an HTTP(S) URL or schemeless domain/path (`https` assumed remotely). The request preserves the original bytes and query-encodes the whole value once. Optional `start_date`, `end_date`, `path_match` (`exact` default or `prefix`) and `per_page` (1–1000, default 100) are omitted when null.

Wistia matches the domain exactly. The path matches exactly or by prefix; an empty or `/` path is ignored, matching all paths on that domain. These are server lookup rules, not client URL fetching or local filtering. Results expose `media_hashed_ids: ?list<string>` ranked by plays; `presentFields` distinguishes an omitted list from a present empty list. IDs can be passed to top-content's `hashed_ids` filter.

Embed-location data is retained for **six months**. An older start date yields remote HTTP 422. Omitted dates use the full six-month queryable window, ending tomorrow to include today. The library validates calendar syntax/order but leaves moving defaults and retention to the server, avoiding a client clock/timezone assumption.

## Failures and verification boundary

Shared sanitized exceptions distinguish API, authentication, permission, rate-limit, transport and decoding failures. `rateLimit` retains supplied headers such as `Retry-After`. Reads are single-attempt, do not follow credential-bearing redirects, and never report an uncertain write outcome. See the [foundation](../foundation.md) for failure handling.

Synthetic offline contracts cover all five paths, authentication/version, exact query encoding, result variants/nulls/zeros, validation bounds, malformed responses and sanitized failures. PHP 8.4 and 8.5 checks use network denial. No Wistia requests, credential provisioning, purchases, trials or destructive calls are part of this delivery. The [inventory](../rewrite/README.md) keeps other operations' implementation and source-gap status separate.
