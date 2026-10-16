<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Jobs\BackgroundJobStatusDecoder;
use Automattic\Wistia\Request\Data\ShowBackgroundJobStatusRequest;
use Automattic\Wistia\Result\Data\ShowBackgroundJobStatusResult;

final readonly class DataBackgroundJobStatusService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function showBackgroundJobStatus(#[\SensitiveParameter] ShowBackgroundJobStatusRequest $request): ShowBackgroundJobStatusResult
    {
        $response = $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/background_job_status/' . Path::segment($request->backgroundJobStatusId), RequestSafety::ReadOnly, [200]),
            new BackgroundJobStatusDecoder());
        return $response->result;
    }
    public function __debugInfo(): array { return []; }
}
