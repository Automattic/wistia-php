<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Stats as R;
use Automattic\Wistia\Result\Stats as D;

final readonly class StatsVisitorsService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function listVisitors(#[\SensitiveParameter] R\ListVisitorsRequest $request): D\ListVisitorsResult
    {
        return $this->client->execute(new Request(ApiFamily::Stats, HttpMethod::Get,
            '/stats/visitors', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ListVisitorsResult::class))->result;
    }

    /** Lazy offset traversal; preserves filters, starts at the requested page, and has a finite bound. */
    public function paginateVisitors(#[\SensitiveParameter] R\ListVisitorsRequest $request, int $maxPages = 1000): Pagination\Paginator
    {
        return new Pagination\Paginator($request->paginationStrategy(),
            function (Encoding\Query $query): Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Stats, HttpMethod::Get,
                    '/stats/visitors', RequestSafety::ReadOnly, [200], $query),
                    new Media\Decoder(D\ListVisitorsResult::class))->result;
                return new Pagination\Page($result->data);
            }, $maxPages);
    }

    public function showVisitor(#[\SensitiveParameter] R\ShowVisitorRequest $request): D\ShowVisitorResult
    {
        return $this->client->execute(new Request(ApiFamily::Stats, HttpMethod::Get,
            '/stats/visitors/' . Path::segment($request->visitorKey), RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowVisitorResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}
