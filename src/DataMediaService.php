<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data\{ListMediaRequest, ShowMediaRequest, UpdateMediaRequest, DeleteMediaRequest, CopyMediaRequest, SwapMediaRequest, ShowMediaAggregatedStatsRequest, ImportMediaFromURLRequest, ArchiveMediaRequest, MoveMediaRequest, RestoreMediaRequest, BulkCopyMediaRequest};
use Automattic\Wistia\Result\Data\{ListMediaResult, ShowMediaResult, UpdateMediaResult, DeleteMediaResult, CopyMediaResult, SwapMediaResult, ShowMediaAggregatedStatsResult, ImportMediaFromURLResult, ArchiveMediaResult, MoveMediaResult, RestoreMediaResult, BulkCopyMediaResult};

final readonly class DataMediaService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function listMedia(#[\SensitiveParameter] ListMediaRequest $request): ListMediaResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/medias', RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(ListMediaResult::class))->result;
    }

    public function showMedia(#[\SensitiveParameter] ShowMediaRequest $request): ShowMediaResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/medias/' . Path::segment($request->mediaHashedId), RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(ShowMediaResult::class))->result;
    }

    public function updateMedia(#[\SensitiveParameter] UpdateMediaRequest $request): UpdateMediaResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/medias/' . Path::segment($request->mediaHashedId), RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(UpdateMediaResult::class))->result;
    }

    public function deleteMedia(#[\SensitiveParameter] DeleteMediaRequest $request): DeleteMediaResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Delete,
            '/medias/' . Path::segment($request->mediaHashedId), RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(DeleteMediaResult::class))->result;
    }

    public function copyMedia(#[\SensitiveParameter] CopyMediaRequest $request): CopyMediaResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/medias/' . Path::segment($request->mediaHashedId) . '/copy', RequestSafety::MayChangeRemoteState, [201],
            $request->query(), $request->body()), new Media\Decoder(CopyMediaResult::class))->result;
    }

    public function swapMedia(#[\SensitiveParameter] SwapMediaRequest $request): SwapMediaResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/medias/' . Path::segment($request->mediaHashedId) . '/swap', RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(SwapMediaResult::class))->result;
    }

    public function showMediaAggregatedStats(#[\SensitiveParameter] ShowMediaAggregatedStatsRequest $request): ShowMediaAggregatedStatsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/medias/' . Path::segment($request->mediaHashedId) . '/stats', RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(ShowMediaAggregatedStatsResult::class))->result;
    }

    public function importMediaFromURL(#[\SensitiveParameter] ImportMediaFromURLRequest $request): ImportMediaFromURLResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/medias/import_url', RequestSafety::MayChangeRemoteState, [201],
            $request->query(), $request->body()), new Media\Decoder(ImportMediaFromURLResult::class))->result;
    }

    public function archiveMedia(#[\SensitiveParameter] ArchiveMediaRequest $request): ArchiveMediaResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/medias/archive', RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(ArchiveMediaResult::class))->result;
    }

    public function moveMedia(#[\SensitiveParameter] MoveMediaRequest $request): MoveMediaResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/medias/move', RequestSafety::MayChangeRemoteState, [200, 207],
            $request->query(), $request->body()), new Media\Decoder(MoveMediaResult::class))->result;
    }

    public function restoreMedia(#[\SensitiveParameter] RestoreMediaRequest $request): RestoreMediaResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/medias/restore', RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(RestoreMediaResult::class))->result;
    }

    public function bulkCopyMedia(#[\SensitiveParameter] BulkCopyMediaRequest $request): BulkCopyMediaResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/medias/copy', RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(BulkCopyMediaResult::class))->result;
    }
    /** Lazy bounded traversal; uses the same typed decoder and preserves every request filter. */
    public function paginateMedia(#[\SensitiveParameter] ListMediaRequest $request, int $maxPages = 1000): \Automattic\Wistia\Pagination\Paginator
    {
        return new \Automattic\Wistia\Pagination\Paginator($request->paginationStrategy(),
            function (\Automattic\Wistia\Encoding\Query $query): \Automattic\Wistia\Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
                    '/medias', RequestSafety::ReadOnly, [200], $query), new Media\Decoder(ListMediaResult::class))->result;
                return \Automattic\Wistia\Pagination\DataCursorPagination::fromRecords($result->data);
            }, $maxPages);
    }
    public function __debugInfo(): array { return []; }
}
