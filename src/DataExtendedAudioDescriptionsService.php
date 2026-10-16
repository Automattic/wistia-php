<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data;
use Automattic\Wistia\Result\Data as Results;

final readonly class DataExtendedAudioDescriptionsService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function listMediaExtendedAudioDescriptions(#[\SensitiveParameter] Data\ListMediaExtendedAudioDescriptionsRequest $request): Results\ListMediaExtendedAudioDescriptionsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/media_extended_audio_descriptions', RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new \Automattic\Wistia\Media\Decoder(Results\ListMediaExtendedAudioDescriptionsResult::class))->result;
    }

    /** Lazy bounded traversal preserving the request filters and ordering. */
    public function paginateMediaExtendedAudioDescriptions(#[\SensitiveParameter] Data\ListMediaExtendedAudioDescriptionsRequest $request, int $maxPages = 1000): \Automattic\Wistia\Pagination\Paginator
    {
        return new \Automattic\Wistia\Pagination\Paginator($request->paginationStrategy(),
            function (\Automattic\Wistia\Encoding\Query $query): \Automattic\Wistia\Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
                    '/media_extended_audio_descriptions', RequestSafety::ReadOnly, [200], $query), new \Automattic\Wistia\Media\Decoder(Results\ListMediaExtendedAudioDescriptionsResult::class))->result;
                return \Automattic\Wistia\Pagination\DataCursorPagination::fromRecords($result->data);
            }, $maxPages);
    }

    public function showMediaExtendedAudioDescription(#[\SensitiveParameter] Data\ShowMediaExtendedAudioDescriptionRequest $request): Results\ShowMediaExtendedAudioDescriptionResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/media_extended_audio_descriptions/' . Path::segment($request->id) . '', RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new \Automattic\Wistia\Media\Decoder(Results\ShowMediaExtendedAudioDescriptionResult::class))->result;
    }

    public function deleteMediaExtendedAudioDescription(#[\SensitiveParameter] Data\DeleteMediaExtendedAudioDescriptionRequest $request): Results\DeleteMediaExtendedAudioDescriptionResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Delete,
            '/media_extended_audio_descriptions/' . Path::segment($request->id) . '', RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new \Automattic\Wistia\Media\Decoder(Results\DeleteMediaExtendedAudioDescriptionResult::class))->result;
    }

    public function getOrderStatus(#[\SensitiveParameter] Data\GetOrderStatusRequest $request): Results\GetOrderStatusResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/media_extended_audio_descriptions/order_status/' . Path::segment($request->id) . '', RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new \Automattic\Wistia\Media\Decoder(Results\GetOrderStatusResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}
