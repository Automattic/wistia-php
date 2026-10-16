<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data as R;
use Automattic\Wistia\Result\Data as D;

final readonly class DataExpiringAccessTokensService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    /** Experimental: upstream may change this contract at any time. */
    public function createExpiringAccessToken(#[\SensitiveParameter] R\CreateExpiringAccessTokenRequest $request): D\CreateExpiringAccessTokenResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/expiring_token', RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(D\CreateExpiringAccessTokenResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}
