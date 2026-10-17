<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Stats;

use Automattic\Wistia\{StatsAccountService, StatsEventsService, StatsMediaService, StatsProjectsService, StatsVisitorsService};
use Automattic\Wistia\Request\Stats as R;

final class StatsCases
{
    public static function fixture(string $operation): string
    {
        return file_get_contents(__DIR__ . '/../../Offline/fixtures/stats/' . $operation . '.json');
    }
    public static function all(): array
    {
        return [
            'ShowCurrentAccountStats' => [StatsAccountService::class, 'showCurrentAccountStats', new R\ShowCurrentAccountStatsRequest(), '/stats/account'],
            'ShowAccountStatsByDate' => [StatsAccountService::class, 'showAccountStatsByDate', new R\ShowAccountStatsByDateRequest('2024-02-29', '2024-03-01'), '/stats/account/by_date?start_date=2024-02-29&end_date=2024-03-01'],
            'ListEvents' => [StatsEventsService::class, 'listEvents', new R\ListEventsRequest(media_id:'22570', visitor_key:'v/a', per_page:2, page:3, start_date:'2024-02-29', end_date:'2024-03-01'), '/stats/events?media_id=22570&visitor_key=v%2Fa&per_page=2&page=3&start_date=2024-02-29&end_date=2024-03-01'],
            'ShowEvent' => [StatsEventsService::class, 'showEvent', new R\ShowEventRequest('event/a'), '/stats/events/event%2Fa'],
            'ShowMediaStats' => [StatsMediaService::class, 'showMediaStats', new R\ShowMediaStatsRequest('22570'), '/stats/medias/22570'],
            'ShowMediaStatsByDate' => [StatsMediaService::class, 'showMediaStatsByDate', new R\ShowMediaStatsByDateRequest('hashed/a', '2024-02-29', '2024-03-01'), '/stats/medias/hashed%2Fa/by_date?start_date=2024-02-29&end_date=2024-03-01'],
            'ShowMediaEngagement' => [StatsMediaService::class, 'showMediaEngagement', new R\ShowMediaEngagementRequest('hashed-media'), '/stats/medias/hashed-media/engagement'],
            'ShowProjectStats' => [StatsProjectsService::class, 'showProjectStats', new R\ShowProjectStatsRequest('folder/a'), '/stats/projects/folder%2Fa'],
            'ListVisitors' => [StatsVisitorsService::class, 'listVisitors', new R\ListVisitorsRequest(page:2, per_page:3, filter:'has_email', search:'a+b@example.invalid'), '/stats/visitors?page=2&per_page=3&filter=has_email&search=a%2Bb%40example.invalid'],
            'ShowVisitor' => [StatsVisitorsService::class, 'showVisitor', new R\ShowVisitorRequest('visitor/a'), '/stats/visitors/visitor%2Fa'],
        ];
    }
}
