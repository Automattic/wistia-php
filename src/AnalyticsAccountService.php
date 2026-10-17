<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Analytics as R;
use Automattic\Wistia\Result\Analytics as D;

final readonly class AnalyticsAccountService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function showAccountAnalytics(#[\SensitiveParameter] R\ShowAccountAnalyticsRequest $request): D\ShowAccountAnalyticsResult
    {
        return $this->client->execute(new Request(ApiFamily::Analytics, HttpMethod::Get,
            '/analytics/account', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowAccountAnalyticsResult::class))->result;
    }

    public function showAccountAnalyticsTimeseries(#[\SensitiveParameter] R\ShowAccountAnalyticsTimeseriesRequest $request): D\ShowAccountAnalyticsTimeseriesResult
    {
        return $this->client->execute(new Request(ApiFamily::Analytics, HttpMethod::Get,
            '/analytics/account/timeseries', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowAccountAnalyticsTimeseriesResult::class))->result;
    }

    public function showAccountTopContent(#[\SensitiveParameter] R\ShowAccountTopContentRequest $request): D\ShowAccountTopContentResult
    {
        return $this->client->execute(new Request(ApiFamily::Analytics, HttpMethod::Get,
            '/analytics/account/top_content', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowAccountTopContentResult::class))->result;
    }

    public function showAccountEmbedLocations(#[\SensitiveParameter] R\ShowAccountEmbedLocationsRequest $request): D\ShowAccountEmbedLocationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Analytics, HttpMethod::Get,
            '/analytics/account/embed_locations', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowAccountEmbedLocationsResult::class))->result;
    }

    public function findMediaByEmbedLocation(#[\SensitiveParameter] R\FindMediaByEmbedLocationRequest $request): D\FindMediaByEmbedLocationResult
    {
        return $this->client->execute(new Request(ApiFamily::Analytics, HttpMethod::Get,
            '/analytics/account/media_by_embed_location', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\FindMediaByEmbedLocationResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}
