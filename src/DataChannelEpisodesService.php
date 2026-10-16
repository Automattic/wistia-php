<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};

final readonly class DataChannelEpisodesService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function showChannelEpisode(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\ShowChannelEpisodeRequest $request): \Automattic\Wistia\Result\Data\ShowChannelEpisodeResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/channels/' . Path::segment((string) $request->channelHashedId) . '/channel_episodes/' . Path::segment((string) $request->channelEpisodeId) . '', RequestSafety::ReadOnly, [200], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\ShowChannelEpisodeResult::class))->result;
    }

    public function listChannelEpisodesByChannel(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\ListChannelEpisodesByChannelRequest $request): \Automattic\Wistia\Result\Data\ListChannelEpisodesByChannelResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/channels/' . Path::segment((string) $request->channelHashedId) . '/channel_episodes', RequestSafety::ReadOnly, [200], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\ListChannelEpisodesByChannelResult::class))->result;
    }

    /** Lazy traversal preserving all endpoint filters, ordering and parent identifiers. */
    public function paginateChannelEpisodesByChannel(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\ListChannelEpisodesByChannelRequest $request, int $maxPages = 1000): \Automattic\Wistia\Pagination\Paginator
    {
        $path = '/channels/' . Path::segment((string) $request->channelHashedId) . '/channel_episodes';
        return new \Automattic\Wistia\Pagination\Paginator($request->paginationStrategy(),
            function (\Automattic\Wistia\Encoding\Query $query) use ($path): \Automattic\Wistia\Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
                    $path, RequestSafety::ReadOnly, [200], $query),
                    new Media\Decoder(\Automattic\Wistia\Result\Data\ListChannelEpisodesByChannelResult::class))->result;
                return \Automattic\Wistia\Pagination\DataCursorPagination::fromRecords($result->data);
            }, $maxPages);
    }

    public function createChannelEpisode(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\CreateChannelEpisodeRequest $request): \Automattic\Wistia\Result\Data\CreateChannelEpisodeResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/channels/' . Path::segment((string) $request->channelHashedId) . '/channel_episodes', RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\CreateChannelEpisodeResult::class))->result;
    }

    public function listChannelEpisodes(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\ListChannelEpisodesRequest $request): \Automattic\Wistia\Result\Data\ListChannelEpisodesResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/channel_episodes', RequestSafety::ReadOnly, [200], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\ListChannelEpisodesResult::class))->result;
    }

    /** Lazy traversal preserving all endpoint filters, ordering and parent identifiers. */
    public function paginateChannelEpisodes(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\ListChannelEpisodesRequest $request, int $maxPages = 1000): \Automattic\Wistia\Pagination\Paginator
    {
        $path = '/channel_episodes';
        return new \Automattic\Wistia\Pagination\Paginator($request->paginationStrategy(),
            function (\Automattic\Wistia\Encoding\Query $query) use ($path): \Automattic\Wistia\Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
                    $path, RequestSafety::ReadOnly, [200], $query),
                    new Media\Decoder(\Automattic\Wistia\Result\Data\ListChannelEpisodesResult::class))->result;
                return \Automattic\Wistia\Pagination\DataCursorPagination::fromRecords($result->data);
            }, $maxPages);
    }

    public function updateChannelEpisode(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\UpdateChannelEpisodeRequest $request): \Automattic\Wistia\Result\Data\UpdateChannelEpisodeResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/channel_episodes/' . Path::segment((string) $request->channelEpisodeHashedId) . '', RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\UpdateChannelEpisodeResult::class))->result;
    }

    public function deleteChannelEpisode(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\DeleteChannelEpisodeRequest $request): \Automattic\Wistia\Result\Data\DeleteChannelEpisodeResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Delete,
            '/channel_episodes/' . Path::segment((string) $request->channelEpisodeHashedId) . '', RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\DeleteChannelEpisodeResult::class))->result;
    }

    public function publishChannelEpisode(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\PublishChannelEpisodeRequest $request): \Automattic\Wistia\Result\Data\PublishChannelEpisodeResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/channel_episodes/' . Path::segment((string) $request->channelEpisodeHashedId) . '/publish', RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\PublishChannelEpisodeResult::class))->result;
    }

    public function unPublishChannelEpisode(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\UnPublishChannelEpisodeRequest $request): \Automattic\Wistia\Result\Data\UnPublishChannelEpisodeResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/channel_episodes/' . Path::segment((string) $request->channelEpisodeHashedId) . '/unpublish', RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\UnPublishChannelEpisodeResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}
