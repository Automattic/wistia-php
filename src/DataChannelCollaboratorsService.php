<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};

final readonly class DataChannelCollaboratorsService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function listChannelCollaborators(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\ListChannelCollaboratorsRequest $request): \Automattic\Wistia\Result\Data\ListChannelCollaboratorsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/channels/' . Path::segment((string) $request->channelHashedId) . '/collaborators', RequestSafety::ReadOnly, [200], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\ListChannelCollaboratorsResult::class))->result;
    }

    /** Lazy traversal preserving all endpoint filters, ordering and parent identifiers. */
    public function paginateChannelCollaborators(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\ListChannelCollaboratorsRequest $request, int $maxPages = 1000): \Automattic\Wistia\Pagination\Paginator
    {
        $path = '/channels/' . Path::segment((string) $request->channelHashedId) . '/collaborators';
        return new \Automattic\Wistia\Pagination\Paginator($request->paginationStrategy(),
            function (\Automattic\Wistia\Encoding\Query $query) use ($path): \Automattic\Wistia\Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
                    $path, RequestSafety::ReadOnly, [200], $query),
                    new Media\Decoder(\Automattic\Wistia\Result\Data\ListChannelCollaboratorsResult::class))->result;
                return \Automattic\Wistia\Pagination\DataCursorPagination::fromRecords($result->data);
            }, $maxPages);
    }

    public function createChannelCollaborator(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\CreateChannelCollaboratorRequest $request): \Automattic\Wistia\Result\Data\CreateChannelCollaboratorResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/channels/' . Path::segment((string) $request->channelHashedId) . '/collaborators', RequestSafety::MayChangeRemoteState, [201], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\CreateChannelCollaboratorResult::class))->result;
    }

    public function deleteChannelCollaborator(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\DeleteChannelCollaboratorRequest $request): \Automattic\Wistia\Result\Data\DeleteChannelCollaboratorResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Delete,
            '/channels/' . Path::segment((string) $request->channelHashedId) . '/collaborators/' . Path::segment((string) $request->id) . '', RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\DeleteChannelCollaboratorResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}
