# Read Stats and Analytics reports

Choose Stats for its account/media/event/visitor metrics, or Analytics for aggregate rankings, timeseries, locations, conversions and webinar reports. Their date and result contracts are not interchangeable.

Use a [configured client](../tutorials/first-api-call.md#create-the-client), **Read detailed stats** permission or suitable delegated access, and any required account feature access. The examples below are read-only.

## Read daily media Stats

```php
use Automattic\Wistia\StatsMediaService;
use Automattic\Wistia\Request\Stats\ShowMediaStatsByDateRequest;

$stats = new StatsMediaService($client);
$daily = $stats->showMediaStatsByDate(new ShowMediaStatsByDateRequest(
    'YOUR_MEDIA_HASHED_ID',
    '2026-01-01',
    '2026-01-31',
));
foreach ($daily->data as $day) {
    $plays = $day->play_count;
}
```

Stats account/media by-date bounds are **inclusive**. Dates are valid `YYYY-MM-DD` calendar values. Stats Events has its own filter contract and documented two-year event retention; do not assume the by-date defaults apply to it.

## Rank account content with Analytics

```php
use Automattic\Wistia\AnalyticsAccountService;
use Automattic\Wistia\Request\Analytics\ShowAccountTopContentRequest;

$analytics = new AnalyticsAccountService($client);
$ranking = $analytics->showAccountTopContent(new ShowAccountTopContentRequest(
    start_date: '2026-01-01',
    end_date: '2026-02-01',
    group_by: 'media',
    sort_by: 'plays',
    per_page: 10,
));
foreach ($ranking->data as $record) {
    $id = $record->media_hashed_id;
    $plays = $record->plays;
}
```

Analytics dates use **inclusive start, exclusive end**. This range covers January. Account/media date-bounded reports enforce at most two calendar years where specified. Webinar post-event filters have their own rules, not that general cap.

Ranking `per_page` is a result limit, not evidence of a next page. `project` grouping means folder, retaining the Analytics wire name.

## Choose a service

| Need | Service |
| --- | --- |
| Account/media Stats and engagement | `StatsAccountService`, `StatsMediaService` |
| Events and visitors | `StatsEventsService`, `StatsVisitorsService` |
| Folder/project Stats | `StatsProjectsService` |
| Account aggregates, rankings, embed domains | `AnalyticsAccountService` |
| Media timeseries, page locations, traffic, languages, conversions | `AnalyticsMediaService` |
| Webinar registrations, traffic, audience and histograms | `AnalyticsWebinarService` |

The [service directory](../reference/services.md) links the full method reference.

## Paginate only supported report lists

Stats events/visitors use offset pagination with a page size capped at 100. Analytics media conversions and webinar audience have scalar-cursor envelopes and lazy bounded companions.

Other aggregate Analytics calls do not expose a cursor just because they accept `per_page`. Use the method's actual shape rather than inventing offset traversal.

Account Analytics embed-location lookup has a six-month retention window, enforced remotely. Account locations aggregate domains; media locations represent pages. Analytics `played_time` is seconds, not Stats' `hours_watched`.

## Handle incomplete or sensitive data

Nullable metrics are not zero. Optional fields can be missing; use `presentFields` where needed. The client does not fill missing time buckets or reinterpret timezones.

Webinar aggregates/histograms can return 409 before the event is terminal; 503 can indicate unavailable analytics. Apply [bounded recovery decisions](errors.md), not an automatic loop.

Visitor identities, email, chat, IPs, custom answers and page URLs need application-owned privacy controls. Do not log entire report records.
