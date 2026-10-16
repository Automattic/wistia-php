<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\{Client, Configuration};
use Automattic\Wistia\Exception\{ApiException, TransportException};
use Automattic\Wistia\Http\{ApiFamily, GuzzleTransport, HttpMethod, JsonDecoder, Request, RequestSafety};
use GuzzleHttp\{Client as GuzzleClient, HandlerStack, Middleware};
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\{Request as PsrRequest, Response};
use PHPUnit\Framework\TestCase;

final class GuzzleTransportTest extends TestCase
{
    public function testRedirectIsNotFollowedAndSecurityOptionsReachHandler(): void
    {
        $history = [];
        $mock = new MockHandler([new Response(302, ['Location' => 'https://elsewhere.invalid/?secret=fixture-secret']), new Response(200, ['Content-Type' => 'application/json'], '{}')]);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));
        $client = new Client(new Configuration('fixture-secret'), new GuzzleTransport(new GuzzleClient(['handler' => $stack])));
        try { $client->execute(new Request(ApiFamily::Data, HttpMethod::Get, '/fixture', RequestSafety::ReadOnly, [200]), new JsonDecoder()); self::fail(); }
        catch (ApiException $e) {
            self::assertSame(302, $e->status);
            ExceptionAssertions::assertSanitized($e);
            self::assertCount(1, $history);
            self::assertSame(1, $mock->count());
            $options = $history[0]['options'];
            self::assertFalse($options['allow_redirects']);
            self::assertFalse($options['http_errors']);
            self::assertFalse($options['debug']);
            self::assertTrue($options['verify']);
            self::assertSame(30.0, $options['timeout']);
            self::assertSame(10.0, $options['connect_timeout']);
        }
    }

    public function testGuzzleTimeoutIsNotReplayedOrRetainedAsAnUnsafeCause(): void
    {
        $history = [];
        $mock = new MockHandler([new ConnectException('fixture-secret', new PsrRequest('POST', 'https://upload.wistia.com/?secret=fixture-secret')), new Response(200)]);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));
        $client = new Client(new Configuration('fixture-secret'), new GuzzleTransport(new GuzzleClient(['handler' => $stack])));
        try { $client->execute(new Request(ApiFamily::Upload, HttpMethod::Post, '/', RequestSafety::MayChangeRemoteState, [200]), new JsonDecoder()); self::fail(); }
        catch (TransportException $e) {
            self::assertTrue($e->remoteOutcomeUncertain);
            self::assertNull($e->getPrevious());
            ExceptionAssertions::assertSanitized($e);
            self::assertCount(1, $history);
            self::assertSame(1, $mock->count());
        }
    }
}
