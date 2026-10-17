# Analytics reference

Five account Analytics operations are implemented for [#26](https://github.com/Automattic/wistia-php/issues/26), and seven media operations for [#27](https://github.com/Automattic/wistia-php/issues/27). Five webinar operations are implemented for [#28](https://github.com/Automattic/wistia-php/issues/28). These are offline-tested contracts, not live-verified account access.

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

## Media Analytics usage

Construct `AnalyticsMediaService` around the same configured `Client`. Each request requires `mediaId` (the video's hashed ID), `start_date` and `end_date`; request/result classes use the same namespaces as the account operations. These explicit services replace legacy magic calls; there is no compatibility layer.

```php
use Automattic\Wistia\AnalyticsMediaService;
use Automattic\Wistia\Request\Analytics\ShowMediaAnalyticsRequest;
use Automattic\Wistia\Request\Analytics\ShowMediaFormConversionsRequest;

$mediaAnalytics = new AnalyticsMediaService($client);
$summary = $mediaAnalytics->showMediaAnalytics(new ShowMediaAnalyticsRequest(
    mediaId: 'video-hashed-id', start_date: '2026-01-01', end_date: '2026-02-01',
));
echo $summary->data->plays;

// Lazy, finite traversal. Handle personal data under your application's privacy policy.
$pages = $mediaAnalytics->paginateMediaFormConversions(new ShowMediaFormConversionsRequest(
    mediaId: 'video-hashed-id', start_date: '2026-01-01', end_date: '2026-02-01', per_page: 25,
), maxPages: 10);
foreach ($pages->pages() as $page) {
    // $page->items contains typed MediaFormConversion records; do not log them.
}
```

All seven media operations send GET to `https://api.wistia.com/modern/analytics/medias/{mediaId}` plus the suffix below, with Bearer authentication and version `2026-09`. The hashed ID is encoded as one path segment. They require **Read detailed stats**, or delegated contact permissions. An expiring delegated token must grant `view-stats` on this media. Plan entitlement and account access remain server decisions; no free-plan or paid-plan live access is asserted.

Every media operation uses inclusive start/exclusive end dates with the strict calendar and two-year boundary described above, including the conservative leap-day clamp. Optional request values are omitted to retain server defaults. Time metrics remain seconds; aggregate/timeseries `played_time` is an integer, while embed-location `played_time` permits fractions. Results expose `status` and typed `data`. Records retain wire names, zero/false/null values and `presentFields` for omission. Properties absent from the source's optional schemas map to null; a present null is rejected where the source does not allow null. Unknown fields, malformed lists/objects, wrong types and non-finite numbers fail decoding. Timestamps are retained as strings without local timezone conversion or bucket filling.

### get_analytics-medias-mediaid

[`showMediaAnalytics(ShowMediaAnalyticsRequest): ShowMediaAnalyticsResult`](https://docs.wistia.com/reference/get_analytics-medias-mediaid.md)

No suffix. `data` is `Analytics\MediaAnalytics` with nullable `unique_loads`, `unique_plays`, `plays`, `play_rate`, `played_time`, `unique_visitors`, `engagement_rate`, `cta_impressions`, `cta_conversions`, `cta_conversion_rate` and `form_conversions`.

### get_analytics-medias-mediaid-timeseries

[`showMediaAnalyticsTimeseries(ShowMediaAnalyticsTimeseriesRequest): ShowMediaAnalyticsTimeseriesResult`](https://docs.wistia.com/reference/get_analytics-medias-mediaid-timeseries.md)

Suffix `/timeseries`. Required `granularity` is `daily`, `weekly` or `monthly`. `data` is `list<Analytics\MediaAnalyticsBucket>` with `timestamp` and the aggregate metrics above.

### get_analytics-medias-mediaid-embed-locations

[`showMediaEmbedLocations(ShowMediaEmbedLocationsRequest): ShowMediaEmbedLocationsResult`](https://docs.wistia.com/reference/get_analytics-medias-mediaid-embed-locations.md)

Suffix `/embed_locations`. Optional `sort_by`: `plays`, `loads`, `engagement_rate`, `play_rate`, `played_time`, `unique_visitors`; optional `sort_direction`: `asc` or `desc`. Defaults are plays/descending. Optional `embed_url` filters one page, accepting an HTTP(S) URL or schemeless domain/path (https assumed remotely). Its original bytes are query-encoded once, not fetched or rewritten. `per_page` is 1–100 (default 10), a limit rather than cursor pagination.

`data` is `list<Analytics\MediaEmbedLocation>` with `embed_domain`, `embed_path`, `embed_url`, `page_title`, `loads`, `unique_loads`, `plays`, `unique_plays`, `play_rate`, `played_time`, `engagement_rate`, `unique_visitors` and `cta_conversion_rate`. These are page-level records, unlike account embed-domain aggregates.

### get_analytics-medias-mediaid-embed-locations-timeseries

[`showMediaEmbedLocationsTimeseries(ShowMediaEmbedLocationsTimeseriesRequest): ShowMediaEmbedLocationsTimeseriesResult`](https://docs.wistia.com/reference/get_analytics-medias-mediaid-embed-locations-timeseries.md)

Suffix `/embed_locations_timeseries` (not `/embed_locations/timeseries`). Required `granularity`: `daily`, `weekly` or `monthly`. Optional `sort_by` and `embed_url` match media embed locations; there is no `sort_direction` input. `per_page` is 1–100 top locations per bucket (default 5).

`data` is `list<Analytics\MediaEmbedLocationBucket>` with `timestamp` and `entries: ?list<MediaEmbedLocation>`. Remaining locations may be combined by Wistia into an **All other** entry: location domain/path/URL are null and page title is `All other`. The library preserves that entry instead of treating it as a missing location or another cursor page.

### get_analytics-medias-mediaid-traffic

[`showMediaTrafficBreakdown(ShowMediaTrafficBreakdownRequest): ShowMediaTrafficBreakdownResult`](https://docs.wistia.com/reference/get_analytics-medias-mediaid-traffic.md)

Suffix `/traffic`. Required `group_by`: `utm_campaign`, `utm_source`, `utm_medium`, `referrer_domain` or `viewer_screen_size`. Optional `sort_by`: `plays`, `loads` or `engagement_rate` (default plays); `sort_direction`: `asc`/`desc` (default desc); `per_page`: 1–100 (default 100).

`data` is `list<Analytics\MediaTrafficGroup>` with the nullable grouping fields plus `loads`, `plays` and `engagement_rate`. A null grouping value is retained. Results are server aggregates, not client regrouping or individual visitor records.

### get_analytics-medias-mediaid-conversions

[`showMediaFormConversions(ShowMediaFormConversionsRequest): ShowMediaFormConversionsResult`](https://docs.wistia.com/reference/get_analytics-medias-mediaid-conversions.md)

Suffix `/conversions`. Optional `per_page`: 1–100 (default 25) and scalar `cursor` from the prior response's `page_info.end_cursor`. When present, `data` is `list<Analytics\MediaFormConversion>` with `first_name`, `last_name`, `email`, `converted_at`, and `custom_fields: ?list<Analytics\MediaFormField>` containing `label`/`value` strings. `page_info` is typed `Analytics\MediaPageInfo`, retaining `has_next_page`, `has_previous_page`, `end_cursor` and `start_cursor`. The source permits either envelope property to be omitted: the result preserves omission as null and records supplied keys in `presentFields`, while rejecting explicit null for these non-nullable wire properties. Single-response reads accept partial envelopes; page construction requires both a data list and page metadata.

`paginateMediaFormConversions(request, maxPages: 1000)` returns the shared lazy paginator: `page(cursor)` fetches one typed page; `pages()` and `items()` traverse with a finite bound. Traversal preserves media ID, dates, page size and starting cursor. Only `has_next_page` and `end_cursor` control continuation, including empty continuing pages; a final page stops regardless of size. An explicit `has_next_page` flag is required; omitted reverse-page metadata defaults to no previous page/null start cursor in the shared page view, while the result's `page_info.presentFields` retains omission. Final pages need no end cursor. Missing/empty continuing cursors, cycles and exhausted bounds fail rather than looping. There is no prefetch, automatic retry, or guessed offset pagination. The other six media operations have no documented cursor envelope or traversal helper.

Conversion names, email, custom answers and cursors are sensitive. Request/result/record/paginator debug output hides values and decode/transport exceptions are sanitized, including captured arguments. Direct property access and explicit serialization still expose data: applications own consent, storage, access control, retention and safe logging. The library does not log, persist, or redact explicitly accessed properties.

### get_analytics-medias-mediaid-languages

[`showMediaLanguages(ShowMediaLanguagesRequest): ShowMediaLanguagesResult`](https://docs.wistia.com/reference/get_analytics-medias-mediaid-languages.md)

Suffix `/languages`. Optional `per_page`: 1–100 (default 100). `data` is `list<Analytics\MediaLanguage>` with `language`, `plays`, `percent_of_total_plays` and `captions_support`. Wistia sorts by plays descending. Language describes the viewer's browser (ISO 639-1); caption availability is a boolean, not a localization purchase or translation request.

## Webinar Analytics usage

Construct `AnalyticsWebinarService` around the configured `Client`. Request/result types use the same Analytics namespaces. All five operations use GET at `https://api.wistia.com/modern/analytics/webinars/{webinarId}`, Bearer authentication and pinned version `2026-09`. The hashed ID is encoded as one path segment; these are Analytics reads, not Data registration writes.

```php
use Automattic\Wistia\AnalyticsWebinarService;
use Automattic\Wistia\Request\Analytics\ShowWebinarAnalyticsRequest;
use Automattic\Wistia\Request\Analytics\ShowWebinarAudienceRequest;

$webinars = new AnalyticsWebinarService($client);
$summary = $webinars->showWebinarAnalytics(new ShowWebinarAnalyticsRequest(
    webinarId: 'webinar-hashed-id', include_post_event: true,
    post_event_start_date: '2026-01-01', post_event_end_date: '2026-02-01',
));
echo $summary->data->registrations;
foreach ($webinars->paginateWebinarAudience(
    new ShowWebinarAudienceRequest('webinar-hashed-id', per_page: 25), maxPages: 10,
)->items() as $attendee) {
    // Typed attendee record; apply your application's personal-data policy.
}
```

Permissions are **Read detailed stats** or suitable delegated contact permissions. Expiring delegated tokens require `view-stats` on this webinar. Account/plan entitlement remains remote: HTTP 403 means the valid token's account lacks feature access; no particular free/paid plan entitlement is asserted. Aggregate analytics and histograms document HTTP 409 until the event reaches a terminal state. HTTP 404 indicates missing/inaccessible webinar; 503 indicates temporarily unavailable analytics. These conditions use shared sanitized exceptions, with no retry or local event-state guess. No live webinar/account access is verified.

All results expose `status` and typed `data`. Optional omitted properties map to null with `presentFields` recording supplied keys. Explicit null is accepted only where the source allows it. Zero counts, fractional metrics, null histogram slots and empty lists are retained; unknown fields, invalid nested shapes, wrong types and non-finite numbers fail decoding. Wire names and timestamp strings remain unchanged. Watch time, event duration and chat positions are **seconds**, without Stats-unit conversion.

### get_analytics-webinars-webinarid

[`showWebinarAnalytics(ShowWebinarAnalyticsRequest): ShowWebinarAnalyticsResult`](https://docs.wistia.com/reference/get_analytics-webinars-webinarid.md)

No suffix. Optional `include_post_event` (server default false), `post_event_start_date` and `post_event_end_date`. Dates use strict calendar YYYY-MM-DD, inclusive start/exclusive end; when both are supplied end must follow start. No account/media two-year cap is imposed. Dates are only used remotely when post-event data is enabled; the library validates and sends supplied dates without silently enabling the flag or rejecting dates with an omitted/false flag. Null options are omitted; explicit false is sent as `false`.

`data` is `Analytics\WebinarAnalytics`: registration/import/on-demand counts, attendance, engagement, watch time, chat/Q&A, impressions/plays, event time and nullable `poll_questions`. Each `WebinarPollQuestion` has `id`, `poll_id`, `text`, `response_count` and nullable typed `options`. Each `WebinarPollOption` retains `id`, `text`, `count` and numeric `percent` without converting percentage units.

### get_analytics-webinars-webinarid-registration

[`showWebinarRegistrationTimeseries(ShowWebinarRegistrationTimeseriesRequest): ShowWebinarRegistrationTimeseriesResult`](https://docs.wistia.com/reference/get_analytics-webinars-webinarid-registration.md)

Suffix `/registration` (singular). Required `granularity`: daily, weekly or monthly. Optional post-event flag/dates follow the aggregate rules above. `data` is a list of `WebinarRegistrationBucket` records: timestamp, registrations, impressions, on-demand registrations/impressions and completion/on-demand completion rates. There is no local timezone conversion or bucket filling.

### get_analytics-webinars-webinarid-traffic

[`showWebinarTrafficBreakdown(ShowWebinarTrafficBreakdownRequest): ShowWebinarTrafficBreakdownResult`](https://docs.wistia.com/reference/get_analytics-webinars-webinarid-traffic.md)

Suffix `/traffic`. Required `group_by`: utm_campaign, utm_source, utm_medium or referrer_domain (not viewer_screen_size). Optional `sort_by`: registrations (default), attendees or impressions; `sort_direction`: asc/desc (default desc). No page size, cursor or post-event/date filters are documented. `data` is a list of `WebinarTrafficGroup` with nullable attribution fields and registration/attendee/impression counts.

### get_analytics-webinars-webinarid-audience

[`showWebinarAudience(ShowWebinarAudienceRequest): ShowWebinarAudienceResult`](https://docs.wistia.com/reference/get_analytics-webinars-webinarid-audience.md)

Suffix `/audience`. Optional `per_page` 1–100 (default 25) and scalar `cursor`. No date, sort or post-event filters are documented. `data` is a list of `WebinarAudienceMember` with identity/contact/company, registration/attendance/restriction/origin, attribution/geolocation, engagement/focus rates, watch/chat/click/poll counts, nullable integer-slot attendance/focused histograms and typed chat buckets. `WebinarChatBucket` holds `rounded_player_position` and a list of chat strings. Chat lists may be omitted but not explicitly null.

The envelope uses the shared typed `MediaPageInfo` wire contract. Omitted `data`/`page_info` remain null in single-response reads; explicit null is invalid. Traversal requires data and an explicit `has_next_page`; optional reverse metadata does not prevent forward traversal. `paginateWebinarAudience(request, maxPages: 1000)` provides lazy `page(cursor)`, `pages()` and `items()`, preserving webinar ID, size and initial cursor. Empty continuing pages follow `end_cursor`; final pages stop regardless of size and need no cursor. Missing/empty/repeated continuing cursors and exhausted finite bounds fail instead of looping. Other webinar operations do not expose pagination.

Audience names, emails, phone numbers, company, locations, attribution, chat text and cursors are sensitive. Records, requests, results and paginator debug output hide values; decoder/transport errors sanitize messages and arguments. Direct property access and explicit serialization still expose data. Applications own consent, access control, storage, retention and logging; the client does not persist or log attendees.

### get_analytics-webinars-webinarid-histograms

[`showWebinarHistograms(ShowWebinarHistogramsRequest): ShowWebinarHistogramsResult`](https://docs.wistia.com/reference/get_analytics-webinars-webinarid-histograms.md)

Suffix `/histograms`. Only webinarId is accepted. `data` is `WebinarHistograms` with numeric `attendees_histogram`, `chat_histogram` and `visual_focus_played_histogram` lists. Each slot represents a **10-second bucket**. Unlike per-attendee histograms, aggregate slots are non-null numbers and preserve fractions; supplied lists cannot be null.

## Failures and verification boundary

Shared sanitized exceptions distinguish API, authentication, permission, rate-limit, transport and decoding failures. `rateLimit` retains supplied headers such as `Retry-After`. Reads are single-attempt, do not follow credential-bearing redirects, and never report an uncertain write outcome. See the [foundation](https://wistia-php.space.fast/guides/reference/configuration.html) for failure handling.

Synthetic offline contracts cover all five account, seven media and five webinar paths, authentication/version, exact query encoding, typed nested results/nulls/zeros, validation bounds, malformed responses and sanitized failures. Media conversion contracts also cover individual pages, empty continuing/final pages, preserved date filters/cursors and bounded traversal errors without logging visitor data. PHP 8.4 and 8.5 checks use network denial. No Wistia requests, credential provisioning, purchases, trials or destructive calls are part of this delivery. The [inventory](../rewrite/README.md) keeps other operations' implementation and source-gap status separate.
