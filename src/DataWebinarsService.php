<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};

final readonly class DataWebinarsService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function listWebinars(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\ListWebinarsRequest $request): \Automattic\Wistia\Result\Data\ListWebinarsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/webinars', RequestSafety::ReadOnly, [200], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\ListWebinarsResult::class))->result;
    }

    /** Lazy traversal preserving filters and ordering, with a finite page bound. */
    public function paginateWebinars(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\ListWebinarsRequest $request, int $maxPages = 1000): \Automattic\Wistia\Pagination\Paginator
    {
        $path = '/webinars';
        return new \Automattic\Wistia\Pagination\Paginator($request->paginationStrategy(),
            function (\Automattic\Wistia\Encoding\Query $query) use ($path, $request): \Automattic\Wistia\Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
                    $path, RequestSafety::ReadOnly, [200], $query),
                    new Media\Decoder(\Automattic\Wistia\Result\Data\ListWebinarsResult::class))->result;
                return \Automattic\Wistia\Pagination\DataCursorPagination::fromRecords($result->data);
            }, $maxPages);
    }

    public function createWebinar(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\CreateWebinarRequest $request): \Automattic\Wistia\Result\Data\CreateWebinarResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/webinars', RequestSafety::MayChangeRemoteState, [201], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\CreateWebinarResult::class))->result;
    }

    public function showWebinar(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\ShowWebinarRequest $request): \Automattic\Wistia\Result\Data\ShowWebinarResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/webinars/' . Path::segment((string) $request->id) . '', RequestSafety::ReadOnly, [200], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\ShowWebinarResult::class))->result;
    }

    public function updateWebinar(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\UpdateWebinarRequest $request): \Automattic\Wistia\Result\Data\UpdateWebinarResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/webinars/' . Path::segment((string) $request->id) . '', RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\UpdateWebinarResult::class))->result;
    }

    public function deleteWebinar(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\DeleteWebinarRequest $request): \Automattic\Wistia\Result\Data\DeleteWebinarResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Delete,
            '/webinars/' . Path::segment((string) $request->id) . '', RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\DeleteWebinarResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}
