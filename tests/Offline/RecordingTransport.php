<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};

final class RecordingTransport implements Transport
{
    /** @var list<TransportRequest> */
    public array $requests = [];
    public ?\Throwable $failure = null;

    public function __construct(public TransportResponse $response) {}

    public function send(#[\SensitiveParameter] TransportRequest $request): TransportResponse
    {
        $this->requests[] = $request;
        if ($this->failure !== null) { throw $this->failure; }
        return $this->response;
    }
}
