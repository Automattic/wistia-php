<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data as R;
use Automattic\Wistia\Result\Data as D;

final readonly class DataBrandsService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function listBrands(#[\SensitiveParameter] R\ListBrandsRequest $request): D\ListBrandsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/brands', RequestSafety::ReadOnly, [200], $request->query(), $request->body()),
            new Media\Decoder(D\ListBrandsResult::class))->result;
    }

    /** Lazy traversal preserving ordering with an explicit finite page bound. */
    public function paginateBrands(#[\SensitiveParameter] R\ListBrandsRequest $request, int $maxPages = 1000): Pagination\Paginator
    {
        return new Pagination\Paginator($request->paginationStrategy(),
            function (Encoding\Query $query): Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
                    '/brands', RequestSafety::ReadOnly, [200], $query),
                    new Media\Decoder(D\ListBrandsResult::class))->result;
                return Pagination\DataCursorPagination::fromRecords($result->data);
            }, $maxPages);
    }

    public function createBrand(#[\SensitiveParameter] R\CreateBrandRequest $request): D\CreateBrandResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/brands', RequestSafety::MayChangeRemoteState, [201], $request->query(), $request->body()),
            new Media\Decoder(D\CreateBrandResult::class))->result;
    }

    public function showBrand(#[\SensitiveParameter] R\ShowBrandRequest $request): D\ShowBrandResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/brands/' . Path::segment($request->brandId), RequestSafety::ReadOnly, [200], $request->query(), $request->body()),
            new Media\Decoder(D\ShowBrandResult::class))->result;
    }

    public function updateBrand(#[\SensitiveParameter] R\UpdateBrandRequest $request): D\UpdateBrandResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/brands/' . Path::segment($request->brandId) . '', RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(D\UpdateBrandResult::class))->result;
    }

    public function deleteBrand(#[\SensitiveParameter] R\DeleteBrandRequest $request): D\DeleteBrandResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Delete,
            '/brands/' . Path::segment($request->brandId) . '', RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(D\DeleteBrandResult::class))->result;
    }

    public function applyBrand(#[\SensitiveParameter] R\ApplyBrandRequest $request): D\ApplyBrandResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/brands/' . Path::segment($request->brandId) . '/apply', RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(D\ApplyBrandResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}
