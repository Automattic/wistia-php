<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Stats as R;
use Automattic\Wistia\Result\Stats as D;

final readonly class StatsEventsService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function listEvents(#[\SensitiveParameter] R\ListEventsRequest $request): D\ListEventsResult
    {
        return $this->client->execute(new Request(ApiFamily::Stats, HttpMethod::Get,
            '/stats/events', RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ListEventsResult::class))->result;
    }

    /** Lazy offset traversal; preserves filters, starts at the requested page, and has a finite bound. */
    public function paginateEvents(#[\SensitiveParameter] R\ListEventsRequest $request, int $maxPages = 1000): Pagination\Paginator
    {
        return new Pagination\Paginator($request->paginationStrategy(),
            function (Encoding\Query $query): Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Stats, HttpMethod::Get,
                    '/stats/events', RequestSafety::ReadOnly, [200], $query),
                    new Media\Decoder(D\ListEventsResult::class))->result;
                return new Pagination\Page($result->data);
            }, $maxPages);
    }

    public function showEvent(#[\SensitiveParameter] R\ShowEventRequest $request): D\ShowEventResult
    {
        return $this->client->execute(new Request(ApiFamily::Stats, HttpMethod::Get,
            '/stats/events/' . Path::segment($request->eventKey), RequestSafety::ReadOnly, [200], $request->query()),
            new Media\Decoder(D\ShowEventResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}
