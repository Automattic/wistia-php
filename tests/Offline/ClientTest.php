<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\{Client, Configuration};
use Automattic\Wistia\Encoding\{Body, Query};
use Automattic\Wistia\Exception\{ApiException, AuthenticationException, DecodeException, PermissionException, RateLimitException, TransportException, WistiaException};
use Automattic\Wistia\Http\{ApiFamily, EmptyDecoder, HttpMethod, JsonDecoder, Request, RequestSafety, TextDecoder, TransportResponse};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    private function transport(int $status = 200, string $body = '{}', array $headers = []): RecordingTransport
    {
        return new RecordingTransport(new TransportResponse($status, $headers + ['Content-Type' => ['application/json']], $body));
    }

    private function request(ApiFamily $family = ApiFamily::Data, HttpMethod $method = HttpMethod::Get,
        RequestSafety $safety = RequestSafety::ReadOnly, array $statuses = [200]): Request
    {
        return new Request($family, $method, '/fixture', $safety, $statuses);
    }

    #[DataProvider('families')]
    public function testHostsAndVersionPolicy(ApiFamily $family, string $base, bool $version, bool $bearer): void
    {
        $transport = $this->transport();
        $client = new Client(new Configuration('fixture-secret'), $transport);
        $client->execute($this->request($family), new JsonDecoder());
        self::assertCount(1, $transport->requests);
        $captured = $transport->requests[0];
        self::assertSame($base . '/fixture', $captured->url);
        self::assertSame($version ? '2026-09' : null, $captured->headers['X-Wistia-API-Version'] ?? null);
        self::assertSame($bearer ? 'Bearer fixture-secret' : null, $captured->headers['Authorization'] ?? null);
        self::assertStringNotContainsString('fixture-secret', $captured->url);
    }

    public static function families(): array
    {
        return [[ApiFamily::Data, 'https://api.wistia.com/modern', true, true],
            [ApiFamily::Stats, 'https://api.wistia.com/modern', true, true],
            [ApiFamily::Analytics, 'https://api.wistia.com/modern', true, true],
            [ApiFamily::Upload, 'https://upload.wistia.com', false, true],
            [ApiFamily::OAuth, 'https://api.wistia.com/oauth', false, false]];
    }

    public function testExactQueryAndJsonBodyAndResponseRateInformation(): void
    {
        $transport = $this->transport(201, '[{"embedCode":false,"fileSize":0}]', ['Retry-After' => ['7'], 'X-RateLimit-Remaining' => ['0']]);
        $client = new Client(new Configuration('fixture-secret'), $transport);
        $request = new Request(ApiFamily::Data, HttpMethod::Post, '/fixture', RequestSafety::MayChangeRemoteState, [201],
            new Query([['tags[]', 'a'], ['tags[]', 'b'], ['cursor[enabled]', 0]]), Body::json(['exactKey' => null]));
        $response = $client->execute($request, new JsonDecoder());
        self::assertSame('https://api.wistia.com/modern/fixture?tags%5B%5D=a&tags%5B%5D=b&cursor%5Benabled%5D=0', $transport->requests[0]->url);
        self::assertSame('{"exactKey":null}', $transport->requests[0]->body);
        self::assertSame('application/json', $transport->requests[0]->headers['Content-Type']);
        self::assertSame(201, $response->status);
        self::assertFalse($response->result->value[0]->embedCode);
        self::assertSame(0, $response->result->value[0]->fileSize);
        self::assertSame('7', $response->rateLimit->retryAfter);
        self::assertSame(0, $response->rateLimit->remaining);
    }

    public function testOAuthTokenlessQueryRemainsUnversioned(): void
    {
        $transport = $this->transport();
        $client = new Client(new Configuration(), $transport);
        $request = new Request(ApiFamily::OAuth, HttpMethod::Post, '/fixture', RequestSafety::MayChangeRemoteState, [200],
            new Query([['client_secret', 'fixture-secret']]));
        $client->execute($request, new JsonDecoder());
        self::assertSame('https://api.wistia.com/oauth/fixture?client_secret=fixture-secret', $transport->requests[0]->url);
        self::assertNull($transport->requests[0]->body);
        self::assertArrayNotHasKey('Authorization', $transport->requests[0]->headers);
        self::assertArrayNotHasKey('X-Wistia-API-Version', $transport->requests[0]->headers);
    }

    public function testUploadFormEncodingIsPassedWithoutAQueryRewrite(): void
    {
        $transport = $this->transport();
        $request = new Request(ApiFamily::Upload, HttpMethod::Post, '/fixture', RequestSafety::MayChangeRemoteState, [200],
            body: Body::form(new Query([['project_id', 0], ['url', 'https://example.invalid/?signature=a+b']])));
        (new Client(new Configuration('fixture-token'), $transport))->execute($request, new JsonDecoder());
        self::assertSame('https://upload.wistia.com/fixture', $transport->requests[0]->url);
        self::assertSame('application/x-www-form-urlencoded', $transport->requests[0]->headers['Content-Type']);
        self::assertSame('project_id=0&url=https%3A%2F%2Fexample.invalid%2F%3Fsignature%3Da%2Bb', $transport->requests[0]->body);
    }

    public function testMissingBearerTokenFailsBeforeTransport(): void
    {
        $transport = $this->transport();
        try { (new Client(new Configuration(), $transport))->execute($this->request(), new JsonDecoder()); self::fail(); }
        catch (\InvalidArgumentException) { self::assertCount(0, $transport->requests); }
    }

    #[DataProvider('errorEnvelopes')]
    public function testErrorsAreStatusBasedAndDiscardSensitiveEnvelopes(int $status, #[\SensitiveParameter] string $body, string $exception): void
    {
        $transport = $this->transport($status, $body, ['Retry-After' => ['fixture-secret'], 'X-Error' => ['fixture-secret']]);
        $client = new Client(new Configuration('fixture-secret'), $transport);
        try { $client->execute($this->request(), new JsonDecoder()); self::fail('Expected sanitized failure.'); }
        catch (WistiaException $e) {
            self::assertInstanceOf($exception, $e);
            self::assertSame($status, $e->status);
            self::assertNull($e->getPrevious());
            self::assertNull($e->rateLimit->retryAfter);
            ExceptionAssertions::assertSanitized($e);
            self::assertCount(1, $transport->requests);
        }
    }

    public static function errorEnvelopes(): array
    {
        return [[400, '{"error":"fixture-secret"}', ApiException::class],
            [400, '{"error":{"code":"fixture-secret","detail":"fixture-secret"}}', ApiException::class],
            [401, '{"code":"fixture-secret","detail":"fixture-secret"}', AuthenticationException::class],
            [401, '<html>fixture-secret</html>', AuthenticationException::class],
            [403, '{"message":"fixture-secret"}', PermissionException::class],
            [429, '{"errors":["fixture-secret"]}', RateLimitException::class],
            [302, 'https://elsewhere.invalid/?token=fixture-secret', ApiException::class],
            [503, 'fixture-secret', ApiException::class]];
    }

    public function testRateLimitErrorExposesValidatedInformation(): void
    {
        $transport = $this->transport(429, 'not JSON', ['Retry-After' => ['Wed, 21 Oct 2015 07:28:00 GMT'],
            'X-RateLimit-Limit' => ['1000'], 'X-RateLimit-Remaining' => ['0'], 'X-RateLimit-Reset' => ['1700000000']]);
        try { (new Client(new Configuration('fixture'), $transport))->execute($this->request(), new JsonDecoder()); self::fail(); }
        catch (RateLimitException $e) {
            self::assertSame('Wed, 21 Oct 2015 07:28:00 GMT', $e->rateLimit->retryAfter);
            self::assertSame(1000, $e->rateLimit->limit);
            self::assertSame(0, $e->rateLimit->remaining);
            self::assertSame(1700000000, $e->rateLimit->reset);
        }
    }

    #[DataProvider('safetyCases')]
    public function testTransportFailureIsSingleAttemptWithExplicitUncertainty(HttpMethod $method, RequestSafety $safety, bool $uncertain): void
    {
        $transport = $this->transport();
        $transport->failure = new \RuntimeException('fixture-secret https://example.invalid/?secret=fixture-secret', 0, new \RuntimeException('nested fixture-secret'));
        $request = new Request(ApiFamily::Upload, $method, '/fixture', $safety, [200],
            new Query([['source', 'https://example.invalid/?sig=fixture-secret']]), Body::json(['secret' => 'fixture-secret']));
        try { (new Client(new Configuration('fixture-secret'), $transport))->execute($request, new JsonDecoder()); self::fail(); }
        catch (TransportException $e) {
            self::assertSame($uncertain, $e->remoteOutcomeUncertain);
            self::assertNull($e->getPrevious());
            ExceptionAssertions::assertSanitized($e);
            self::assertCount(1, $transport->requests);
        }
    }

    public static function safetyCases(): array
    {
        return [[HttpMethod::Get, RequestSafety::ReadOnly, false], [HttpMethod::Post, RequestSafety::MayChangeRemoteState, true],
            [HttpMethod::Get, RequestSafety::MayChangeRemoteState, true], [HttpMethod::Delete, RequestSafety::ReadOnly, true]];
    }

    public function testMalformedSuccessAfterWriteAlsoReportsUncertainty(): void
    {
        $transport = $this->transport(200, '{fixture-secret');
        try { (new Client(new Configuration('fixture-secret'), $transport))->execute($this->request(method: HttpMethod::Post), new JsonDecoder()); self::fail(); }
        catch (DecodeException $e) {
            self::assertTrue($e->remoteOutcomeUncertain);
            self::assertNull($e->getPrevious());
            ExceptionAssertions::assertSanitized($e);
            self::assertCount(1, $transport->requests);
        }
    }

    public function testTextAndNoContentSuccessDoNotAssumeJson(): void
    {
        $transport = $this->transport(200, 'WEBVTT', ['Content-Type' => ['text/vtt; charset=utf-8']]);
        $client = new Client(new Configuration('fixture'), $transport);
        self::assertSame('WEBVTT', $client->execute($this->request(), new TextDecoder('text/vtt'))->result->value);
        self::assertArrayNotHasKey('Accept', $transport->requests[0]->headers);
        $transport->response = new TransportResponse(204, [], '');
        self::assertSame(204, $client->execute($this->request(statuses: [204]), new EmptyDecoder())->status);
    }

    public function testUndeclaredSuccessStatusIsNotAccepted(): void
    {
        $transport = $this->transport(202, '{}');
        $this->expectException(DecodeException::class);
        (new Client(new Configuration('fixture'), $transport))->execute($this->request(), new JsonDecoder());
    }
}
