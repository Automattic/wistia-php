<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data;
use Automattic\Wistia\Result\Data as Results;

final readonly class DataCaptionsService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function listCaptionsByMedia(#[\SensitiveParameter] Data\ListCaptionsByMediaRequest $request): Results\ListCaptionsByMediaResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/medias/' . Path::segment($request->mediaHashedId) . '/captions', RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new \Automattic\Wistia\Media\Decoder(Results\ListCaptionsByMediaResult::class))->result;
    }

    public function createCaptions(#[\SensitiveParameter] Data\CreateCaptionsRequest $request): Results\CreateCaptionsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/medias/' . Path::segment($request->mediaHashedId) . '/captions', RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new \Automattic\Wistia\Media\Decoder(Results\CreateCaptionsResult::class, false))->result;
    }

    public function listCaptions(#[\SensitiveParameter] Data\ListCaptionsRequest $request): Results\ListCaptionsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/captions', RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new \Automattic\Wistia\Media\Decoder(Results\ListCaptionsResult::class))->result;
    }

    /** Lazy bounded traversal preserving the request filters and ordering. */
    public function paginateCaptions(#[\SensitiveParameter] Data\ListCaptionsRequest $request, int $maxPages = 1000): \Automattic\Wistia\Pagination\Paginator
    {
        return new \Automattic\Wistia\Pagination\Paginator($request->paginationStrategy(),
            function (\Automattic\Wistia\Encoding\Query $query): \Automattic\Wistia\Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
                    '/captions', RequestSafety::ReadOnly, [200], $query), new \Automattic\Wistia\Media\Decoder(Results\ListCaptionsResult::class))->result;
                return \Automattic\Wistia\Pagination\DataCursorPagination::fromRecords($result->data);
            }, $maxPages);
    }

    public function findCaptionMatches(#[\SensitiveParameter] Data\FindCaptionMatchesRequest $request): Results\FindCaptionMatchesResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/caption_matches', RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new \Automattic\Wistia\Media\Decoder(Results\FindCaptionMatchesResult::class))->result;
    }

    public function showCaptions(#[\SensitiveParameter] Data\ShowCaptionsRequest $request): Results\ShowCaptionsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/medias/' . Path::segment($request->mediaHashedId) . '/captions/' . Path::segment($request->languageCode) . '' . $request->format->suffix(),
            $request->include === 'diarized_segments' ? RequestSafety::MayChangeRemoteState : RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new \Automattic\Wistia\Accessibility\CaptionDecoder($request->format))->result;
    }

    public function updateCaptions(#[\SensitiveParameter] Data\UpdateCaptionsRequest $request): Results\UpdateCaptionsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/medias/' . Path::segment($request->mediaHashedId) . '/captions/' . Path::segment($request->languageCode) . '', RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new \Automattic\Wistia\Media\Decoder(Results\UpdateCaptionsResult::class, false))->result;
    }

    public function deleteCaptions(#[\SensitiveParameter] Data\DeleteCaptionsRequest $request): Results\DeleteCaptionsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Delete,
            '/medias/' . Path::segment($request->mediaHashedId) . '/captions/' . Path::segment($request->languageCode) . '', RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new \Automattic\Wistia\Media\Decoder(Results\DeleteCaptionsResult::class, false))->result;
    }

    public function editCaptionsText(#[\SensitiveParameter] Data\EditCaptionsTextRequest $request): Results\EditCaptionsTextResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/medias/' . Path::segment($request->mediaHashedId) . '/captions/' . Path::segment($request->languageCode) . '/edits', RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new \Automattic\Wistia\Media\Decoder(Results\EditCaptionsTextResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}
