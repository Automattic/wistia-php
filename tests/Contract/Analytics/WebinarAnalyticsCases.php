<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Analytics;

use Automattic\Wistia\AnalyticsWebinarService;
use Automattic\Wistia\Request\Analytics as R;

final class WebinarAnalyticsCases
{
    public static function fixture(string $operation): string
    {
        return file_get_contents(__DIR__ . '/../../Offline/fixtures/analytics/' . $operation . '.json');
    }
    public static function all(): array
    {
        $path = '/analytics/webinars/w%2Fa%2Bb';
        return [
            'ShowWebinarAnalytics' => [AnalyticsWebinarService::class, 'showWebinarAnalytics', new R\ShowWebinarAnalyticsRequest('w/a+b', true, '2024-02-29', '2024-03-01'), $path.'?include_post_event=true&post_event_start_date=2024-02-29&post_event_end_date=2024-03-01'],
            'ShowWebinarRegistrationTimeseries' => [AnalyticsWebinarService::class, 'showWebinarRegistrationTimeseries', new R\ShowWebinarRegistrationTimeseriesRequest('w/a+b', 'weekly', false, '2024-02-29', '2024-03-01'), $path.'/registration?granularity=weekly&include_post_event=false&post_event_start_date=2024-02-29&post_event_end_date=2024-03-01'],
            'ShowWebinarTrafficBreakdown' => [AnalyticsWebinarService::class, 'showWebinarTrafficBreakdown', new R\ShowWebinarTrafficBreakdownRequest('w/a+b', 'utm_source', 'attendees', 'asc'), $path.'/traffic?group_by=utm_source&sort_by=attendees&sort_direction=asc'],
            'ShowWebinarAudience' => [AnalyticsWebinarService::class, 'showWebinarAudience', new R\ShowWebinarAudienceRequest('w/a+b', 100, 'c+/='), $path.'/audience?per_page=100&cursor=c%2B%2F%3D'],
            'ShowWebinarHistograms' => [AnalyticsWebinarService::class, 'showWebinarHistograms', new R\ShowWebinarHistogramsRequest('w/a+b'), $path.'/histograms'],
        ];
    }
}
