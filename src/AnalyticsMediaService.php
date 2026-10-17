<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Analytics as R;
use Automattic\Wistia\Result\Analytics as D;

final readonly class AnalyticsMediaService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function showMediaAnalytics(#[\SensitiveParameter] R\ShowMediaAnalyticsRequest $request): D\ShowMediaAnalyticsResult
    {
        return $this->client->execute(new Request(ApiFamily::Analytics, HttpMethod::Get,
            '/analytics/medias/' . Path::segment($request->mediaId), RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowMediaAnalyticsResult::class))->result;
    }

    public function showMediaAnalyticsTimeseries(#[\SensitiveParameter] R\ShowMediaAnalyticsTimeseriesRequest $request): D\ShowMediaAnalyticsTimeseriesResult
    {
        return $this->client->execute(new Request(ApiFamily::Analytics, HttpMethod::Get,
            '/analytics/medias/' . Path::segment($request->mediaId) . '/timeseries', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowMediaAnalyticsTimeseriesResult::class))->result;
    }

    public function showMediaEmbedLocations(#[\SensitiveParameter] R\ShowMediaEmbedLocationsRequest $request): D\ShowMediaEmbedLocationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Analytics, HttpMethod::Get,
            '/analytics/medias/' . Path::segment($request->mediaId) . '/embed_locations', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowMediaEmbedLocationsResult::class))->result;
    }

    public function showMediaEmbedLocationsTimeseries(#[\SensitiveParameter] R\ShowMediaEmbedLocationsTimeseriesRequest $request): D\ShowMediaEmbedLocationsTimeseriesResult
    {
        return $this->client->execute(new Request(ApiFamily::Analytics, HttpMethod::Get,
            '/analytics/medias/' . Path::segment($request->mediaId) . '/embed_locations_timeseries', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowMediaEmbedLocationsTimeseriesResult::class))->result;
    }

    public function showMediaTrafficBreakdown(#[\SensitiveParameter] R\ShowMediaTrafficBreakdownRequest $request): D\ShowMediaTrafficBreakdownResult
    {
        return $this->client->execute(new Request(ApiFamily::Analytics, HttpMethod::Get,
            '/analytics/medias/' . Path::segment($request->mediaId) . '/traffic', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowMediaTrafficBreakdownResult::class))->result;
    }

    public function showMediaFormConversions(#[\SensitiveParameter] R\ShowMediaFormConversionsRequest $request): D\ShowMediaFormConversionsResult
    {
        return $this->client->execute(new Request(ApiFamily::Analytics, HttpMethod::Get,
            '/analytics/medias/' . Path::segment($request->mediaId) . '/conversions', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowMediaFormConversionsResult::class))->result;
    }

    public function showMediaLanguages(#[\SensitiveParameter] R\ShowMediaLanguagesRequest $request): D\ShowMediaLanguagesResult
    {
        return $this->client->execute(new Request(ApiFamily::Analytics, HttpMethod::Get,
            '/analytics/medias/' . Path::segment($request->mediaId) . '/languages', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowMediaLanguagesResult::class))->result;
    }

    /** Lazy conversion traversal preserving dates, size and starting cursor; no prefetch or retry. */
    public function paginateMediaFormConversions(#[\SensitiveParameter] R\ShowMediaFormConversionsRequest $request, int $maxPages = 1000): Pagination\Paginator
    {
        // Reject invalid bounds before a cursor-bearing strategy can enter constructor traces.
        if ($maxPages < 1) { throw new \InvalidArgumentException('A positive finite page bound is required.'); }
        $path = '/analytics/medias/' . Path::segment($request->mediaId) . '/conversions';
        return new Pagination\Paginator($request->paginationStrategy(),
            function (#[\SensitiveParameter] Encoding\Query $query) use ($path): Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Analytics, HttpMethod::Get,
                    $path, RequestSafety::ReadOnly, [200], $query),
                    new Media\Decoder(D\ShowMediaFormConversionsResult::class))->result;
                return $result->page();
            }, $maxPages);
    }
    public function __debugInfo(): array { return []; }
}
