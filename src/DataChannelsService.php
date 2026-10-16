<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};

final readonly class DataChannelsService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function listChannels(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\ListChannelsRequest $request): \Automattic\Wistia\Result\Data\ListChannelsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/channels', RequestSafety::ReadOnly, [200], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\ListChannelsResult::class))->result;
    }

    /** Lazy traversal preserving all endpoint filters, ordering and parent identifiers. */
    public function paginateChannels(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\ListChannelsRequest $request, int $maxPages = 1000): \Automattic\Wistia\Pagination\Paginator
    {
        $path = '/channels';
        return new \Automattic\Wistia\Pagination\Paginator($request->paginationStrategy(),
            function (\Automattic\Wistia\Encoding\Query $query) use ($path): \Automattic\Wistia\Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
                    $path, RequestSafety::ReadOnly, [200], $query),
                    new Media\Decoder(\Automattic\Wistia\Result\Data\ListChannelsResult::class))->result;
                return \Automattic\Wistia\Pagination\DataCursorPagination::fromRecords($result->data);
            }, $maxPages);
    }

    public function createChannel(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\CreateChannelRequest $request): \Automattic\Wistia\Result\Data\CreateChannelResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/channels', RequestSafety::MayChangeRemoteState, [201], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\CreateChannelResult::class))->result;
    }

    public function showChannel(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\ShowChannelRequest $request): \Automattic\Wistia\Result\Data\ShowChannelResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/channels/' . Path::segment((string) $request->channelHashedId) . '', RequestSafety::ReadOnly, [200], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\ShowChannelResult::class))->result;
    }

    public function updateChannel(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\UpdateChannelRequest $request): \Automattic\Wistia\Result\Data\UpdateChannelResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/channels/' . Path::segment((string) $request->channelHashedId) . '', RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\UpdateChannelResult::class))->result;
    }

    public function deleteChannel(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\DeleteChannelRequest $request): \Automattic\Wistia\Result\Data\DeleteChannelResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Delete,
            '/channels/' . Path::segment((string) $request->channelHashedId) . '', RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\DeleteChannelResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}
