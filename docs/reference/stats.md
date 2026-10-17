# Stats API

Ten read-only operations are implemented for [#25](https://github.com/Automattic/wistia-php/issues/25). They use `https://api.wistia.com/modern`, Bearer authentication and pinned `X-Wistia-API-Version: 2026-09`. Construct each service around `Client`; request/result classes live under `Automattic\Wistia\Request\Stats` and `Automattic\Wistia\Result\Stats`, and nested records under `Automattic\Wistia\Stats`.

## Usage

```php
use Automattic\Wistia\StatsMediaService;
use Automattic\Wistia\StatsEventsService;
use Automattic\Wistia\Request\Stats\ShowMediaStatsByDateRequest;
use Automattic\Wistia\Request\Stats\ListEventsRequest;

// $client is the configured Client from the root README.
$daily = (new StatsMediaService($client))->showMediaStatsByDate(
    new ShowMediaStatsByDateRequest('media-hashed-id', '2024-02-29', '2024-03-01')
);
foreach ($daily->data as $day) {
    echo $day->date, ': ', $day->play_count, PHP_EOL;
}

$events = new StatsEventsService($client);
foreach ($events->paginateEvents(new ListEventsRequest(media_id: 'media-hashed-id', per_page: 100), maxPages: 20)->items() as $event) {
    // Treat visitor identities, IPs, page URLs and raw attributes as sensitive data.
    $key = $event->event_key;
}
```

## Contracts and restrictions

Every operation requires a token with **Read detailed stats**, or a delegated contact token whose permissions grant the relevant access. Expiring delegated tokens need `view-stats` on the account for account/events/visitors, on the media for media stats, or on the folder for project stats. The library does not establish permissions or plan entitlement locally. Failures use the shared authentication, permission, API, rate-limit, transport and decoding exceptions. Supplied rate-limit headers remain accessible. Requests are single-attempt, with no automatic retry or redirects.

By-date account/media bounds are **inclusive**. Omit both to let Wistia select its documented yesterday/today default; the library does not calculate a timezone-dependent window. One-sided dates pass through unchanged. Calendar dates must use valid `YYYY-MM-DD`; reversed ranges fail locally. List Events also accepts date filters; its source does not specify the same inclusive/default-window behavior, so this guide makes no such promise for Events. Only events from the last **two years** are documented as available.

Event and visitor lists use offset `page`/`per_page`, not cursors. Both must be positive and `per_page` is capped at 100. `paginateEvents` and `paginateVisitors` are lazy, preserve all supplied filters and start at the requested page. A short/empty page ends traversal; a full page advances. A positive finite `maxPages` bounds requests; exhausting it raises `OverflowException`. `items()` yields records; `pages()` yields pages. Reaching a local bound does not imply a complete export.

Results expose `data` and `status`. Array responses remain lists, not envelopes. Nested records preserve wire names, optional-field presence in `presentFields`, explicit nullable fields, zeros and false values. An absent optional field is represented as `null` but is absent from `presentFields`; a present nonnullable field cannot contain JSON null. Date/timestamp response strings are preserved. Event `conversion_type` accepts integer or string, and `attributes` is the documented open JSON object (`stdClass`); the surrounding records remain explicitly typed. Thumbnail `fileSize` and `contentType` keep their source spelling. `number_of_videos` is retained for project stats but is deprecated upstream.

## get_stats-account

`StatsAccountService::showCurrentAccountStats(ShowCurrentAccountStatsRequest): ShowCurrentAccountStatsResult` — `GET /stats/account`. Returns `AccountStats` with required integer `load_count`/`play_count` and numeric `hours_watched`.

[First-party contract](https://docs.wistia.com/reference/get_stats-account.md)

## get_stats-account-by-date

`StatsAccountService::showAccountStatsByDate(ShowAccountStatsByDateRequest): ShowAccountStatsByDateResult` — `GET /stats/account/by_date`. Optional `start_date`, `end_date`; returns a list of `DailyStats` (`date`, `load_count`, `play_count`, `hours_watched`).

[First-party contract](https://docs.wistia.com/reference/get_stats-account-by-date.md)

## get_stats-events

`StatsEventsService::listEvents(ListEventsRequest): ListEventsResult` — `GET /stats/events`. Optional `media_id`, `visitor_key`, `per_page`, `page`, `start_date`, `end_date`; returns a list of `Event`. `paginateEvents` traverses it. Use a media identifier for `media_id`, not an event key.

[First-party contract](https://docs.wistia.com/reference/get_stats-events.md)

## get_stats-events-eventkey

`StatsEventsService::showEvent(ShowEventRequest): ShowEventResult` — `GET /stats/events/{eventKey}`. Requires the event's key, not the visitor key or media ID; returns `Event` with typed thumbnail, conversion and user-agent records plus open raw attributes.

[First-party contract](https://docs.wistia.com/reference/get_stats-events-eventkey.md)

## get_stats-medias-mediaid

`StatsMediaService::showMediaStats(ShowMediaStatsRequest): ShowMediaStatsResult` — `GET /stats/medias/{mediaId}`. Accepts a hashed or numeric media ID as a string. Returns `MediaStats`, including loads/plays, play rate, hours, engagement, visitor count and typed action metrics.

[First-party contract](https://docs.wistia.com/reference/get_stats-medias-mediaid.md)

## get_stats-medias-mediaid-by-date

`StatsMediaService::showMediaStatsByDate(ShowMediaStatsByDateRequest): ShowMediaStatsByDateResult` — `GET /stats/medias/{mediaId}/by_date`. Requires `mediaId`; optional `start_date`, `end_date`; returns a list of `DailyStats`.

[First-party contract](https://docs.wistia.com/reference/get_stats-medias-mediaid-by-date.md)

## get_stats-medias-mediaid-engagement

`StatsMediaService::showMediaEngagement(ShowMediaEngagementRequest): ShowMediaEngagementResult` — `GET /stats/medias/{mediaId}/engagement`. Accepts a hashed or numeric media ID as a string. Returns `MediaEngagement` with numeric `engagement` and integer lists `engagement_data` and `rewatch_data`.

[First-party contract](https://docs.wistia.com/reference/get_stats-medias-mediaid-engagement.md)

## get_stats-projects-projectid

`StatsProjectsService::showProjectStats(ShowProjectStatsRequest): ShowProjectStatsResult` — `GET /stats/projects/{projectId}`. Accepts a hashed or numeric project/folder ID as a string and returns `ProjectStats`. The Stats path remains **projects**, despite the modern Data API's folder rename; do not replace it with `/stats/folders`.

[First-party contract](https://docs.wistia.com/reference/get_stats-projects-projectid.md)

## get_stats-visitors

`StatsVisitorsService::listVisitors(ListVisitorsRequest): ListVisitorsResult` — `GET /stats/visitors`. Optional `page`, `per_page`, `filter`, `search`; `filter` is `has_name`, `has_email` or `identified_by_email_gate`. Search text is preserved and percent-encoded once. Returns a list of `Visitor`; `paginateVisitors` traverses it.

[First-party contract](https://docs.wistia.com/reference/get_stats-visitors.md)

## get_stats-visitors-visitorkey

`StatsVisitorsService::showVisitor(ShowVisitorRequest): ShowVisitorResult` — `GET /stats/visitors/{visitorKey}`. Requires a visitor key, not a media ID or event key. Returns `Visitor` with typed nullable identity/organization fields and user-agent details.

[First-party contract](https://docs.wistia.com/reference/get_stats-visitors-visitorkey.md)

## Migration and evidence

Replace legacy magic Stats calls and raw arrays with the explicit services and named requests above, then read typed result `data`. No old-interface compatibility layer is provided. The [inventory](../rewrite/operations.json) maps each operation to its method, request/result, this reference and its individual offline contract test. Those tests and the synthetic fixtures in `tests/Offline/fixtures/stats` model wire contracts; they are not captured production responses. Runtime results belong to the delivery handoff. No live API, account permission, paid-plan entitlement or retention behavior has been verified against Wistia.
