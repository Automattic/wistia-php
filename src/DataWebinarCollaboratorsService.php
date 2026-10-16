<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};

final readonly class DataWebinarCollaboratorsService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function listWebinarCollaborators(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\ListWebinarCollaboratorsRequest $request): \Automattic\Wistia\Result\Data\ListWebinarCollaboratorsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/webinars/' . Path::segment((string) $request->webinarId) . '/collaborators', RequestSafety::ReadOnly, [200], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\ListWebinarCollaboratorsResult::class))->result;
    }

    /** Lazy traversal preserving filters and ordering, with a finite page bound. */
    public function paginateWebinarCollaborators(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\ListWebinarCollaboratorsRequest $request, int $maxPages = 1000): \Automattic\Wistia\Pagination\Paginator
    {
        $path = '/webinars/' . Path::segment((string) $request->webinarId) . '/collaborators';
        return new \Automattic\Wistia\Pagination\Paginator($request->paginationStrategy(),
            function (\Automattic\Wistia\Encoding\Query $query) use ($path, $request): \Automattic\Wistia\Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
                    $path, RequestSafety::ReadOnly, [200], $query),
                    new Media\Decoder(\Automattic\Wistia\Result\Data\ListWebinarCollaboratorsResult::class))->result;
                return \Automattic\Wistia\Pagination\DataCursorPagination::fromRecords($result->data);
            }, $maxPages);
    }

    public function createWebinarCollaborator(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\CreateWebinarCollaboratorRequest $request): \Automattic\Wistia\Result\Data\CreateWebinarCollaboratorResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/webinars/' . Path::segment((string) $request->webinarId) . '/collaborators', RequestSafety::MayChangeRemoteState, [201], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\CreateWebinarCollaboratorResult::class))->result;
    }

    public function deleteWebinarCollaborator(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\DeleteWebinarCollaboratorRequest $request): \Automattic\Wistia\Result\Data\DeleteWebinarCollaboratorResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Delete,
            '/webinars/' . Path::segment((string) $request->webinarId) . '/collaborators/' . Path::segment((string) $request->id) . '', RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\DeleteWebinarCollaboratorResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}
