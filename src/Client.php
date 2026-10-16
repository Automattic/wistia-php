<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Exception\{ApiException, AuthenticationException, DecodeException, PermissionException, RateLimitException, TransportException};
use Automattic\Wistia\Http\{ApiFamily, GuzzleTransport, RateLimitInfo, Request, Response, ResponseDecoder, Transport, TransportRequest};

/** Shared execution foundation. Endpoint services are delivered by subsequent tickets. */
final class Client
{
    private Transport $transport;

    public function __construct(
        #[\SensitiveParameter] private readonly Configuration $configuration,
        #[\SensitiveParameter] ?Transport $transport = null,
    ) {
        $this->transport = $transport ?? new GuzzleTransport();
    }

    public function execute(#[\SensitiveParameter] Request $request, #[\SensitiveParameter] ResponseDecoder $decoder): Response
    {
        $headers = [];
        if ($request->family !== ApiFamily::OAuth) {
            $headers['Authorization'] = $this->configuration->authorization();
        }
        if ($request->family->usesVersion()) {
            $headers['X-Wistia-API-Version'] = Configuration::API_VERSION;
        }
        if ($request->body !== null) { $headers['Content-Type'] = $request->body->contentType; }
        $query = $request->query->encode();
        $url = $request->family->baseUri() . $request->path . ($query !== '' ? '?' . $query : '');
        try {
            $response = $this->transport->send(new TransportRequest($request->method->value, $url, $headers,
                $request->body?->contents(), $this->configuration->timeout, $this->configuration->connectTimeout));
        } catch (\Throwable) {
            throw TransportException::create(uncertain: $request->mayChangeRemoteState());
        }
        $rate = RateLimitInfo::fromResponse($response);
        if ($response->status < 200 || $response->status >= 300) {
            $error = match ($response->status) {
                401 => AuthenticationException::class,
                403 => PermissionException::class,
                429 => RateLimitException::class,
                default => ApiException::class,
            };
            throw $error::create($response->status, $rate,
                $request->mayChangeRemoteState() && ($response->status === 408 || $response->status >= 500 || ($response->status >= 300 && $response->status < 400)));
        }
        if (!in_array($response->status, $request->successStatuses, true)) {
            throw DecodeException::create($response->status, $rate, $request->mayChangeRemoteState());
        }
        try {
            return new Response($decoder->decode($response), $response->status, $rate);
        } catch (\Throwable) {
            throw DecodeException::create($response->status, $rate, $request->mayChangeRemoteState());
        }
    }

    public function __debugInfo(): array { return []; }
    public function __serialize(): array { throw new \LogicException('Clients cannot be serialized.'); }
}
