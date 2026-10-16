<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data;
use Automattic\Wistia\Result\Data as Results;

final readonly class DataLocalizationsService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function listLocalizations(#[\SensitiveParameter] Data\ListLocalizationsRequest $request): Results\ListLocalizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/medias/' . Path::segment($request->mediaHashedId) . '/localizations', RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new \Automattic\Wistia\Media\Decoder(Results\ListLocalizationsResult::class))->result;
    }

    public function showLocalization(#[\SensitiveParameter] Data\ShowLocalizationRequest $request): Results\ShowLocalizationResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/medias/' . Path::segment($request->mediaHashedId) . '/localizations/' . Path::segment($request->localizationHashedId) . '', RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new \Automattic\Wistia\Media\Decoder(Results\ShowLocalizationResult::class))->result;
    }

    public function deleteLocalization(#[\SensitiveParameter] Data\DeleteLocalizationRequest $request): Results\DeleteLocalizationResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Delete,
            '/medias/' . Path::segment($request->mediaHashedId) . '/localizations/' . Path::segment($request->localizationHashedId) . '', RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new \Automattic\Wistia\Media\Decoder(Results\DeleteLocalizationResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}
