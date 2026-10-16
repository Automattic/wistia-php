<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data\{BulkTagMediaRequest};
use Automattic\Wistia\Result\Data\{BulkTagMediaResult};

final readonly class DataTaggingsService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function bulkTagMedia(#[\SensitiveParameter] BulkTagMediaRequest $request): BulkTagMediaResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/taggings/bulk_create', RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(BulkTagMediaResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}
