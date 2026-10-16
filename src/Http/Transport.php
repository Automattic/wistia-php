<?php
declare(strict_types=1);

namespace Automattic\Wistia\Http;

/** Injected transports must send exactly once, without redirects, retries or request logging. */
interface Transport
{
    public function send(#[\SensitiveParameter] TransportRequest $request): TransportResponse;
}
