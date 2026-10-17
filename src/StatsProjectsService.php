<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Stats as R;
use Automattic\Wistia\Result\Stats as D;

final readonly class StatsProjectsService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function showProjectStats(#[\SensitiveParameter] R\ShowProjectStatsRequest $request): D\ShowProjectStatsResult
    {
        return $this->client->execute(new Request(ApiFamily::Stats, HttpMethod::Get,
            '/stats/projects/' . Path::segment($request->projectId), RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowProjectStatsResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}
