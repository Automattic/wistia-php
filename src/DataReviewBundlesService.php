<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data as R;
use Automattic\Wistia\Result\Data as D;

final readonly class DataReviewBundlesService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function listReviewBundles(#[\SensitiveParameter] R\ListReviewBundlesRequest $request): D\ListReviewBundlesResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/review_bundles', RequestSafety::ReadOnly, [200], $request->query(), $request->body()),
            new Media\Decoder(D\ListReviewBundlesResult::class))->result;
    }

    public function createReviewBundle(#[\SensitiveParameter] R\CreateReviewBundleRequest $request): D\CreateReviewBundleResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/review_bundles', RequestSafety::MayChangeRemoteState, [201], $request->query(), $request->body()),
            new Media\Decoder(D\CreateReviewBundleResult::class))->result;
    }

    public function deleteReviewBundle(#[\SensitiveParameter] R\DeleteReviewBundleRequest $request): D\DeleteReviewBundleResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Delete,
            '/review_bundles/' . Path::segment($request->reviewBundleHashedId), RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(D\DeleteReviewBundleResult::class))->result;
    }
    /** Lazy traversal preserving every filter and sorting value, bounded by maxPages. */
    public function paginateReviewBundles(#[\SensitiveParameter] R\ListReviewBundlesRequest $request, int $maxPages = 1000): Pagination\Paginator
    {
        return new Pagination\Paginator($request->paginationStrategy(),
            function (Encoding\Query $query) use ($request): Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
                    '/review_bundles', RequestSafety::ReadOnly, [200], $query),
                    new Media\Decoder(D\ListReviewBundlesResult::class))->result;
                return $request->cursor?->active()
                    ? Pagination\DataCursorPagination::fromRecords($result->data)
                    : new Pagination\Page($result->data);
            }, $maxPages);
    }
    public function __debugInfo(): array { return []; }
}
