<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data as R;
use Automattic\Wistia\Result\Data as D;

final readonly class DataAllowedDomainsService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function listAllowedDomains(#[\SensitiveParameter] R\ListAllowedDomainsRequest $request): D\ListAllowedDomainsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/allowed_domains', RequestSafety::ReadOnly, [200], $request->query(), $request->body()),
            new Media\Decoder(D\ListAllowedDomainsResult::class))->result;
    }

    public function createAllowedDomain(#[\SensitiveParameter] R\CreateAllowedDomainRequest $request): D\CreateAllowedDomainResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/allowed_domains', RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(D\CreateAllowedDomainResult::class))->result;
    }

    public function showAllowedDomain(#[\SensitiveParameter] R\ShowAllowedDomainRequest $request): D\ShowAllowedDomainResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/allowed_domains/' . Path::segment($request->domain), RequestSafety::ReadOnly, [200], $request->query(), $request->body()),
            new Media\Decoder(D\ShowAllowedDomainResult::class))->result;
    }

    public function deleteAllowedDomain(#[\SensitiveParameter] R\DeleteAllowedDomainRequest $request): D\DeleteAllowedDomainResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Delete,
            '/allowed_domains/' . Path::segment($request->domain), RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(D\DeleteAllowedDomainResult::class))->result;
    }
    /** Lazy traversal preserving ordering with an explicit finite page bound. */
    public function paginateAllowedDomains(#[\SensitiveParameter] R\ListAllowedDomainsRequest $request, int $maxPages = 1000): Pagination\Paginator
    {
        return new Pagination\Paginator($request->paginationStrategy(),
            function (Encoding\Query $query): Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
                    '/allowed_domains', RequestSafety::ReadOnly, [200], $query),
                    new Media\Decoder(D\ListAllowedDomainsResult::class))->result;
                return Pagination\DataCursorPagination::fromRecords($result->data);
            }, $maxPages);
    }

    public function __debugInfo(): array { return []; }
}
