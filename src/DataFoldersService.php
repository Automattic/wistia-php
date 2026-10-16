<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data\{ListFoldersRequest, CreateFolderRequest, ShowFolderRequest, UpdateFolderRequest, DeleteFolderRequest, CopyFolderRequest};
use Automattic\Wistia\Result\Data\{ListFoldersResult, CreateFolderResult, ShowFolderResult, UpdateFolderResult, DeleteFolderResult, CopyFolderResult};

final readonly class DataFoldersService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function listFolders(#[\SensitiveParameter] ListFoldersRequest $request): ListFoldersResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/folders', RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(ListFoldersResult::class))->result;
    }

    public function createFolder(#[\SensitiveParameter] CreateFolderRequest $request): CreateFolderResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/folders', RequestSafety::MayChangeRemoteState, [201],
            $request->query(), $request->body()), new Media\Decoder(CreateFolderResult::class))->result;
    }

    public function showFolder(#[\SensitiveParameter] ShowFolderRequest $request): ShowFolderResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/folders/' . Path::segment($request->id) . '', RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(ShowFolderResult::class))->result;
    }

    public function updateFolder(#[\SensitiveParameter] UpdateFolderRequest $request): UpdateFolderResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/folders/' . Path::segment($request->id) . '', RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(UpdateFolderResult::class))->result;
    }

    public function deleteFolder(#[\SensitiveParameter] DeleteFolderRequest $request): DeleteFolderResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Delete,
            '/folders/' . Path::segment($request->id) . '', RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(DeleteFolderResult::class))->result;
    }

    public function copyFolder(#[\SensitiveParameter] CopyFolderRequest $request): CopyFolderResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/folders/' . Path::segment($request->id) . '/copy', RequestSafety::MayChangeRemoteState, [202],
            $request->query(), $request->body()), new Media\Decoder(CopyFolderResult::class))->result;
    }
    /** Lazy traversal preserving filters, sorting and nested parent identifiers. */
    public function paginateFolders(#[\SensitiveParameter] ListFoldersRequest $request, int $maxPages = 1000): \Automattic\Wistia\Pagination\Paginator
    {
        $path = '/folders';
        return new \Automattic\Wistia\Pagination\Paginator($request->paginationStrategy(),
            function (\Automattic\Wistia\Encoding\Query $query) use ($path): \Automattic\Wistia\Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
                    $path, RequestSafety::ReadOnly, [200], $query), new Media\Decoder(ListFoldersResult::class))->result;
                return \Automattic\Wistia\Pagination\DataCursorPagination::fromRecords($result->data);
            }, $maxPages);
    }
    public function __debugInfo(): array { return []; }
}
