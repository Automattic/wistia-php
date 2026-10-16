<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data\{ListDeletedMediaRequest, RestoreDeletedMediaRequest};
use Automattic\Wistia\Result\Data\{ListDeletedMediaResult, RestoreDeletedMediaResult};

final readonly class DataDeletedMediaService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function listDeletedMedia(#[\SensitiveParameter] ListDeletedMediaRequest $request): ListDeletedMediaResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/deleted_media', RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(ListDeletedMediaResult::class))->result;
    }

    public function restoreDeletedMedia(#[\SensitiveParameter] RestoreDeletedMediaRequest $request): RestoreDeletedMediaResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/deleted_media/restore', RequestSafety::MayChangeRemoteState, [202],
            $request->query(), $request->body()), new Media\Decoder(RestoreDeletedMediaResult::class))->result;
    }
    /** Lazy bounded traversal; uses the same typed decoder and preserves every request filter. */
    public function paginateDeletedMedia(#[\SensitiveParameter] ListDeletedMediaRequest $request, int $maxPages = 1000): \Automattic\Wistia\Pagination\Paginator
    {
        return new \Automattic\Wistia\Pagination\Paginator($request->paginationStrategy(),
            function (\Automattic\Wistia\Encoding\Query $query): \Automattic\Wistia\Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
                    '/deleted_media', RequestSafety::ReadOnly, [200], $query), new Media\Decoder(ListDeletedMediaResult::class))->result;
                return \Automattic\Wistia\Pagination\DataCursorPagination::fromRecords($result->data);
            }, $maxPages);
    }
    public function __debugInfo(): array { return []; }
}
