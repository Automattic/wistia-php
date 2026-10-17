<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Stats as R;
use Automattic\Wistia\Result\Stats as D;

final readonly class StatsAccountService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function showCurrentAccountStats(#[\SensitiveParameter] R\ShowCurrentAccountStatsRequest $request): D\ShowCurrentAccountStatsResult
    {
        return $this->client->execute(new Request(ApiFamily::Stats, HttpMethod::Get,
            '/stats/account', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowCurrentAccountStatsResult::class))->result;
    }

    public function showAccountStatsByDate(#[\SensitiveParameter] R\ShowAccountStatsByDateRequest $request): D\ShowAccountStatsByDateResult
    {
        return $this->client->execute(new Request(ApiFamily::Stats, HttpMethod::Get,
            '/stats/account/by_date', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowAccountStatsByDateResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}
