<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data as R;
use Automattic\Wistia\Result\Data as D;

final readonly class DataResourceURLsService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function resolveResourceURLs(#[\SensitiveParameter] R\ResolveResourceURLsRequest $request): D\ResolveResourceURLsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/resource_urls', RequestSafety::ReadOnly, [200], $request->query(), $request->body()),
            new Media\Decoder(D\ResolveResourceURLsResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}
