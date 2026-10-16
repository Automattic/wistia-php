<?php
declare(strict_types=1);

namespace Automattic\Wistia\Http;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;

final class GuzzleTransport implements Transport
{
    private ClientInterface $client;

    /** A supplied client/handler is trusted: it must not install retry, redirect or logging middleware. */
    public function __construct(#[\SensitiveParameter] ?ClientInterface $client = null)
    {
        $this->client = $client ?? new Client(['handler' => HandlerStack::create(new SingleAttemptCurlHandler())]);
    }

    public function send(#[\SensitiveParameter] TransportRequest $request): TransportResponse
    {
        $response = $this->client->request($request->method, $request->url, [
            'headers' => $request->headers,
            'body' => $request->body,
            'timeout' => $request->timeout,
            'connect_timeout' => $request->connectTimeout,
            'allow_redirects' => false,
            'http_errors' => false,
            'verify' => true,
            'cookies' => false,
            'debug' => false,
        ]);
        return new TransportResponse($response->getStatusCode(), $response->getHeaders(), (string) $response->getBody());
    }

    public function __debugInfo(): array { return []; }
}
