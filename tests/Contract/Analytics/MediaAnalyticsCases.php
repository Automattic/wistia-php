<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Analytics;

use Automattic\Wistia\AnalyticsMediaService;
use Automattic\Wistia\Request\Analytics as R;

final class MediaAnalyticsCases
{
    public static function fixture(string $operation): string
    {
        return file_get_contents(__DIR__ . '/../../Offline/fixtures/analytics/' . $operation . '.json');
    }
    public static function all(): array
    {
        $path = '/analytics/medias/m%2Fa%2Bb';
        $dates = '?start_date=2024-02-29&end_date=2024-03-01';
        return [
            'ShowMediaAnalytics' => [AnalyticsMediaService::class, 'showMediaAnalytics', new R\ShowMediaAnalyticsRequest('m/a+b', '2024-02-29', '2024-03-01'), $path.$dates],
            'ShowMediaAnalyticsTimeseries' => [AnalyticsMediaService::class, 'showMediaAnalyticsTimeseries', new R\ShowMediaAnalyticsTimeseriesRequest('m/a+b', '2024-02-29', '2024-03-01', 'weekly'), $path.'/timeseries'.$dates.'&granularity=weekly'],
            'ShowMediaEmbedLocations' => [AnalyticsMediaService::class, 'showMediaEmbedLocations', new R\ShowMediaEmbedLocationsRequest('m/a+b', '2024-02-29', '2024-03-01', 'played_time', 'asc', 'example.invalid/p?q=a+b&x=%2F', 100), $path.'/embed_locations'.$dates.'&sort_by=played_time&sort_direction=asc&embed_url=example.invalid%2Fp%3Fq%3Da%2Bb%26x%3D%252F&per_page=100'],
            'ShowMediaEmbedLocationsTimeseries' => [AnalyticsMediaService::class, 'showMediaEmbedLocationsTimeseries', new R\ShowMediaEmbedLocationsTimeseriesRequest('m/a+b', '2024-02-29', '2024-03-01', 'monthly', 'unique_visitors', 'https://example.invalid/p', 100), $path.'/embed_locations_timeseries'.$dates.'&granularity=monthly&sort_by=unique_visitors&embed_url=https%3A%2F%2Fexample.invalid%2Fp&per_page=100'],
            'ShowMediaTrafficBreakdown' => [AnalyticsMediaService::class, 'showMediaTrafficBreakdown', new R\ShowMediaTrafficBreakdownRequest('m/a+b', '2024-02-29', '2024-03-01', 'utm_campaign', 'engagement_rate', 'desc', 100), $path.'/traffic'.$dates.'&group_by=utm_campaign&sort_by=engagement_rate&sort_direction=desc&per_page=100'],
            'ShowMediaFormConversions' => [AnalyticsMediaService::class, 'showMediaFormConversions', new R\ShowMediaFormConversionsRequest('m/a+b', '2024-02-29', '2024-03-01', 100, 'c+/='), $path.'/conversions'.$dates.'&per_page=100&cursor=c%2B%2F%3D'],
            'ShowMediaLanguages' => [AnalyticsMediaService::class, 'showMediaLanguages', new R\ShowMediaLanguagesRequest('m/a+b', '2024-02-29', '2024-03-01', 100), $path.'/languages'.$dates.'&per_page=100'],
        ];
    }
}
