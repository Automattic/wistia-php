<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data\{ListTagsRequest, CreateTagsRequest, DeleteTagRequest};
use Automattic\Wistia\Result\Data\{ListTagsResult, CreateTagsResult, DeleteTagResult};

final readonly class DataTagsService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function listTags(#[\SensitiveParameter] ListTagsRequest $request): ListTagsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/tags', RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(ListTagsResult::class))->result;
    }

    public function createTags(#[\SensitiveParameter] CreateTagsRequest $request): CreateTagsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/tags', RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(CreateTagsResult::class))->result;
    }

    public function deleteTag(#[\SensitiveParameter] DeleteTagRequest $request): DeleteTagResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Delete,
            '/tags/' . Path::segment($request->name) . '', RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(DeleteTagResult::class, false))->result;
    }
    /** Lazy bounded traversal; uses the same typed decoder and preserves every request filter. */
    public function paginateTags(#[\SensitiveParameter] ListTagsRequest $request, int $maxPages = 1000): \Automattic\Wistia\Pagination\Paginator
    {
        return new \Automattic\Wistia\Pagination\Paginator($request->paginationStrategy(),
            function (\Automattic\Wistia\Encoding\Query $query): \Automattic\Wistia\Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
                    '/tags', RequestSafety::ReadOnly, [200], $query), new Media\Decoder(ListTagsResult::class))->result;
                return \Automattic\Wistia\Pagination\DataCursorPagination::fromRecords($result->data);
            }, $maxPages);
    }
    public function __debugInfo(): array { return []; }
}
