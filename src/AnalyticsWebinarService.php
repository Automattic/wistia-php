<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Analytics as R;
use Automattic\Wistia\Result\Analytics as D;

final readonly class AnalyticsWebinarService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function showWebinarAnalytics(#[\SensitiveParameter] R\ShowWebinarAnalyticsRequest $request): D\ShowWebinarAnalyticsResult
    {
        return $this->client->execute(new Request(ApiFamily::Analytics, HttpMethod::Get,
            '/analytics/webinars/' . Path::segment($request->webinarId) . '', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowWebinarAnalyticsResult::class))->result;
    }

    public function showWebinarRegistrationTimeseries(#[\SensitiveParameter] R\ShowWebinarRegistrationTimeseriesRequest $request): D\ShowWebinarRegistrationTimeseriesResult
    {
        return $this->client->execute(new Request(ApiFamily::Analytics, HttpMethod::Get,
            '/analytics/webinars/' . Path::segment($request->webinarId) . '/registration', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowWebinarRegistrationTimeseriesResult::class))->result;
    }

    public function showWebinarTrafficBreakdown(#[\SensitiveParameter] R\ShowWebinarTrafficBreakdownRequest $request): D\ShowWebinarTrafficBreakdownResult
    {
        return $this->client->execute(new Request(ApiFamily::Analytics, HttpMethod::Get,
            '/analytics/webinars/' . Path::segment($request->webinarId) . '/traffic', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowWebinarTrafficBreakdownResult::class))->result;
    }

    public function showWebinarAudience(#[\SensitiveParameter] R\ShowWebinarAudienceRequest $request): D\ShowWebinarAudienceResult
    {
        return $this->client->execute(new Request(ApiFamily::Analytics, HttpMethod::Get,
            '/analytics/webinars/' . Path::segment($request->webinarId) . '/audience', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowWebinarAudienceResult::class))->result;
    }

    public function showWebinarHistograms(#[\SensitiveParameter] R\ShowWebinarHistogramsRequest $request): D\ShowWebinarHistogramsResult
    {
        return $this->client->execute(new Request(ApiFamily::Analytics, HttpMethod::Get,
            '/analytics/webinars/' . Path::segment($request->webinarId) . '/histograms', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowWebinarHistogramsResult::class))->result;
    }

    /** Lazy audience traversal preserving size and starting cursor; no prefetch or retry. */
    public function paginateWebinarAudience(#[\SensitiveParameter] R\ShowWebinarAudienceRequest $request, int $maxPages = 1000): Pagination\Paginator
    {
        // Reject invalid bounds before a cursor-bearing strategy can enter constructor traces.
        if ($maxPages < 1) { throw new \InvalidArgumentException('A positive finite page bound is required.'); }
        $path = '/analytics/webinars/' . Path::segment($request->webinarId) . '/audience';
        return new Pagination\Paginator($request->paginationStrategy(),
            function (#[\SensitiveParameter] Encoding\Query $query) use ($path): Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Analytics, HttpMethod::Get,
                    $path, RequestSafety::ReadOnly, [200], $query),
                    new Media\Decoder(D\ShowWebinarAudienceResult::class))->result;
                return $result->page();
            }, $maxPages);
    }
    public function __debugInfo(): array { return []; }
}
