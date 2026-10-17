<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Analytics;

use Automattic\Wistia\AnalyticsAccountService;
use Automattic\Wistia\Request\Analytics as R;

final class AccountAnalyticsCases
{
    public static function fixture(string $operation): string
    {
        return file_get_contents(__DIR__ . '/../../Offline/fixtures/analytics/' . $operation . '.json');
    }
    public static function all(): array
    {
        return [
            'ShowAccountAnalytics' => [AnalyticsAccountService::class, 'showAccountAnalytics', new R\ShowAccountAnalyticsRequest('2024-02-29', '2024-03-01'), '/analytics/account?start_date=2024-02-29&end_date=2024-03-01'],
            'ShowAccountAnalyticsTimeseries' => [AnalyticsAccountService::class, 'showAccountAnalyticsTimeseries', new R\ShowAccountAnalyticsTimeseriesRequest('2024-02-29', '2024-03-01', 'weekly'), '/analytics/account/timeseries?start_date=2024-02-29&end_date=2024-03-01&granularity=weekly'],
            'ShowAccountTopContent' => [AnalyticsAccountService::class, 'showAccountTopContent', new R\ShowAccountTopContentRequest('2024-02-29', '2024-03-01', 'media', ['m/a', 'm+b'], 'played_time', 'asc', 1000), '/analytics/account/top_content?start_date=2024-02-29&end_date=2024-03-01&group_by=media&hashed_ids%5B%5D=m%2Fa&hashed_ids%5B%5D=m%2Bb&sort_by=played_time&sort_direction=asc&per_page=1000'],
            'ShowAccountEmbedLocations' => [AnalyticsAccountService::class, 'showAccountEmbedLocations', new R\ShowAccountEmbedLocationsRequest('2024-02-29', '2024-03-01', 'unique_visitors', 'desc', 100), '/analytics/account/embed_locations?start_date=2024-02-29&end_date=2024-03-01&sort_by=unique_visitors&sort_direction=desc&per_page=100'],
            'FindMediaByEmbedLocation' => [AnalyticsAccountService::class, 'findMediaByEmbedLocation', new R\FindMediaByEmbedLocationRequest('example.invalid/pricing?q=a+b&x=%2F', '2024-02-29', '2024-03-01', 'prefix', 1000), '/analytics/account/media_by_embed_location?embed_url=example.invalid%2Fpricing%3Fq%3Da%2Bb%26x%3D%252F&start_date=2024-02-29&end_date=2024-03-01&path_match=prefix&per_page=1000'],
        ];
    }
}
