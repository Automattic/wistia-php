<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data\CreateBulkActionsRequest;
use Automattic\Wistia\Result\Data\CreateBulkActionsResult;

final readonly class DataBulkActionsService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}
    public function createBulkActions(#[\SensitiveParameter] CreateBulkActionsRequest $request): CreateBulkActionsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/bulk', RequestSafety::MayChangeRemoteState, [202], $request->query(), $request->body()),
            new Bulk\Decoder())->result;
    }
    public function __debugInfo(): array { return []; }
}
