<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data\{ListFolderSharingsRequest, CreateFolderSharingRequest, ShowFolderSharingRequest, UpdateFolderSharingRequest, DeleteFolderSharingRequest};
use Automattic\Wistia\Result\Data\{ListFolderSharingsResult, CreateFolderSharingResult, ShowFolderSharingResult, UpdateFolderSharingResult, DeleteFolderSharingResult};

final readonly class DataFolderSharingsService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function listFolderSharings(#[\SensitiveParameter] ListFolderSharingsRequest $request): ListFolderSharingsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/folders/' . Path::segment($request->folderId) . '/sharings', RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(ListFolderSharingsResult::class))->result;
    }

    public function createFolderSharing(#[\SensitiveParameter] CreateFolderSharingRequest $request): CreateFolderSharingResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/folders/' . Path::segment($request->folderId) . '/sharings', RequestSafety::MayChangeRemoteState, [201],
            $request->query(), $request->body()), new Media\Decoder(CreateFolderSharingResult::class))->result;
    }

    public function showFolderSharing(#[\SensitiveParameter] ShowFolderSharingRequest $request): ShowFolderSharingResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/folders/' . Path::segment($request->folderId) . '/sharings/' . Path::segment((string) $request->sharingId), RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(ShowFolderSharingResult::class))->result;
    }

    public function updateFolderSharing(#[\SensitiveParameter] UpdateFolderSharingRequest $request): UpdateFolderSharingResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/folders/' . Path::segment($request->folderId) . '/sharings/' . Path::segment($request->sharingId) . '', RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(UpdateFolderSharingResult::class))->result;
    }

    public function deleteFolderSharing(#[\SensitiveParameter] DeleteFolderSharingRequest $request): DeleteFolderSharingResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Delete,
            '/folders/' . Path::segment($request->folderId) . '/sharings/' . Path::segment($request->sharingId) . '', RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(DeleteFolderSharingResult::class))->result;
    }
    /** Lazy traversal preserving filters, sorting and nested parent identifiers. */
    public function paginateFolderSharings(#[\SensitiveParameter] ListFolderSharingsRequest $request, int $maxPages = 1000): \Automattic\Wistia\Pagination\Paginator
    {
        $path = '/folders/' . Path::segment($request->folderId) . '/sharings';
        return new \Automattic\Wistia\Pagination\Paginator($request->paginationStrategy(),
            function (\Automattic\Wistia\Encoding\Query $query) use ($path): \Automattic\Wistia\Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
                    $path, RequestSafety::ReadOnly, [200], $query), new Media\Decoder(ListFolderSharingsResult::class))->result;
                return \Automattic\Wistia\Pagination\DataCursorPagination::fromRecords($result->data);
            }, $maxPages);
    }
    public function __debugInfo(): array { return []; }
}
