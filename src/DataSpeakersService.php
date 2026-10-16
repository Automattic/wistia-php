<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data;
use Automattic\Wistia\Result\Data as Results;

final readonly class DataSpeakersService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function listSpeakers(#[\SensitiveParameter] Data\ListSpeakersRequest $request): Results\ListSpeakersResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/speakers', RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new \Automattic\Wistia\Media\Decoder(Results\ListSpeakersResult::class))->result;
    }

    /** Lazy bounded traversal preserving the request filters and ordering. */
    public function paginateSpeakers(#[\SensitiveParameter] Data\ListSpeakersRequest $request, int $maxPages = 1000): \Automattic\Wistia\Pagination\Paginator
    {
        return new \Automattic\Wistia\Pagination\Paginator($request->paginationStrategy(),
            function (\Automattic\Wistia\Encoding\Query $query): \Automattic\Wistia\Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
                    '/speakers', RequestSafety::ReadOnly, [200], $query), new \Automattic\Wistia\Media\Decoder(Results\ListSpeakersResult::class))->result;
                return \Automattic\Wistia\Pagination\DataCursorPagination::fromRecords($result->data);
            }, $maxPages);
    }
    public function __debugInfo(): array { return []; }
}
