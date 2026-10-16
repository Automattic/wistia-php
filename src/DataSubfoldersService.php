<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data\{ListSubfoldersRequest, CreateSubfolderRequest, ShowSubfolderRequest, UpdateSubfolderRequest, DeleteSubfolderRequest, BulkDeleteSubfoldersRequest};
use Automattic\Wistia\Result\Data\{ListSubfoldersResult, CreateSubfolderResult, ShowSubfolderResult, UpdateSubfolderResult, DeleteSubfolderResult, BulkDeleteSubfoldersResult};

final readonly class DataSubfoldersService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function listSubfolders(#[\SensitiveParameter] ListSubfoldersRequest $request): ListSubfoldersResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/folders/' . Path::segment($request->folderId) . '/subfolders', RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(ListSubfoldersResult::class))->result;
    }

    public function createSubfolder(#[\SensitiveParameter] CreateSubfolderRequest $request): CreateSubfolderResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/folders/' . Path::segment($request->folderId) . '/subfolders', RequestSafety::MayChangeRemoteState, [201],
            $request->query(), $request->body()), new Media\Decoder(CreateSubfolderResult::class))->result;
    }

    public function showSubfolder(#[\SensitiveParameter] ShowSubfolderRequest $request): ShowSubfolderResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/folders/' . Path::segment($request->folderId) . '/subfolders/' . Path::segment($request->subfolderId) . '', RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(ShowSubfolderResult::class))->result;
    }

    public function updateSubfolder(#[\SensitiveParameter] UpdateSubfolderRequest $request): UpdateSubfolderResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/folders/' . Path::segment($request->folderId) . '/subfolders/' . Path::segment($request->subfolderId) . '', RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(UpdateSubfolderResult::class))->result;
    }

    public function deleteSubfolder(#[\SensitiveParameter] DeleteSubfolderRequest $request): DeleteSubfolderResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Delete,
            '/folders/' . Path::segment($request->folderId) . '/subfolders/' . Path::segment($request->subfolderId) . '', RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(DeleteSubfolderResult::class))->result;
    }

    public function bulkDeleteSubfolders(#[\SensitiveParameter] BulkDeleteSubfoldersRequest $request): BulkDeleteSubfoldersResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Delete,
            '/folders/' . Path::segment($request->folderId) . '/subfolders/bulk_delete', RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(BulkDeleteSubfoldersResult::class))->result;
    }
    /** Lazy traversal preserving filters, sorting and nested parent identifiers. */
    public function paginateSubfolders(#[\SensitiveParameter] ListSubfoldersRequest $request, int $maxPages = 1000): \Automattic\Wistia\Pagination\Paginator
    {
        $path = '/folders/' . Path::segment($request->folderId) . '/subfolders';
        return new \Automattic\Wistia\Pagination\Paginator($request->paginationStrategy(),
            function (\Automattic\Wistia\Encoding\Query $query) use ($path): \Automattic\Wistia\Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
                    $path, RequestSafety::ReadOnly, [200], $query), new Media\Decoder(ListSubfoldersResult::class))->result;
                return \Automattic\Wistia\Pagination\DataCursorPagination::fromRecords($result->data);
            }, $maxPages);
    }
    public function __debugInfo(): array { return []; }
}
