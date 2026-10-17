<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Stats as R;
use Automattic\Wistia\Result\Stats as D;

final readonly class StatsMediaService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function showMediaStats(#[\SensitiveParameter] R\ShowMediaStatsRequest $request): D\ShowMediaStatsResult
    {
        return $this->client->execute(new Request(ApiFamily::Stats, HttpMethod::Get,
            '/stats/medias/' . Path::segment($request->mediaId), RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowMediaStatsResult::class))->result;
    }

    public function showMediaStatsByDate(#[\SensitiveParameter] R\ShowMediaStatsByDateRequest $request): D\ShowMediaStatsByDateResult
    {
        return $this->client->execute(new Request(ApiFamily::Stats, HttpMethod::Get,
            '/stats/medias/' . Path::segment($request->mediaId) . '/by_date', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowMediaStatsByDateResult::class))->result;
    }

    public function showMediaEngagement(#[\SensitiveParameter] R\ShowMediaEngagementRequest $request): D\ShowMediaEngagementResult
    {
        return $this->client->execute(new Request(ApiFamily::Stats, HttpMethod::Get,
            '/stats/medias/' . Path::segment($request->mediaId) . '/engagement', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowMediaEngagementResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}
