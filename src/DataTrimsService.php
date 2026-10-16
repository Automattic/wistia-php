<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data\{CreateMediaFromTrimsRequest};
use Automattic\Wistia\Result\Data\{CreateMediaFromTrimsResult};

final readonly class DataTrimsService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function createMediaFromTrims(#[\SensitiveParameter] CreateMediaFromTrimsRequest $request): CreateMediaFromTrimsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/medias/' . Path::segment($request->mediaHashedId) . '/trims', RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(CreateMediaFromTrimsResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}
