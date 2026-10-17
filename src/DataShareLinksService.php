<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data as R;
use Automattic\Wistia\Result\Data as D;

final readonly class DataShareLinksService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function resolveShareLink(#[\SensitiveParameter] R\ResolveShareLinkRequest $request): D\ResolveShareLinkResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/share_links/' . Path::segment($request->identifier), RequestSafety::ReadOnly, [200], $request->query(), $request->body()),
            new Media\Decoder(D\ResolveShareLinkResult::class))->result;
    }

    public function showShareLink(#[\SensitiveParameter] R\ShowShareLinkRequest $request): D\ShowShareLinkResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/medias/' . Path::segment($request->mediaId) . '/share_link', RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(D\ShowShareLinkResult::class))->result;
    }

    public function updateShareLink(#[\SensitiveParameter] R\UpdateShareLinkRequest $request): D\UpdateShareLinkResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/medias/' . Path::segment($request->mediaId) . '/share_link', RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(D\UpdateShareLinkResult::class))->result;
    }

    public function deleteShareLink(#[\SensitiveParameter] R\DeleteShareLinkRequest $request): D\DeleteShareLinkResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Delete,
            '/medias/' . Path::segment($request->mediaId) . '/share_link', RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(D\DeleteShareLinkResult::class, false))->result;
    }
    public function __debugInfo(): array { return []; }
}
