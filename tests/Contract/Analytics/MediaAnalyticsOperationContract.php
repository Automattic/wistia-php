<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Analytics;

use Automattic\Wistia\{Client, Configuration};
use Automattic\Wistia\Exception\{ApiException, AuthenticationException, DecodeException, PermissionException, RateLimitException, TransportException, WistiaException};
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Result\Result;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

abstract class MediaAnalyticsOperationContract extends TestCase
{
    protected const OPERATION = '';
    protected function invoke(int $status = 200, #[\SensitiveParameter] ?string $body = null, bool $disconnect = false, string $type = 'application/json'): array
    {
        [$class,$method,$input] = MediaAnalyticsCases::all()[static::OPERATION];
        $transport = new class($status, $body ?? MediaAnalyticsCases::fixture(static::OPERATION), $disconnect, $type) implements Transport {
            public array $requests = [];
            public function __construct(private int $status, private string $body, private bool $disconnect, private string $type) {}
            public function send(TransportRequest $request): TransportResponse {
                $this->requests[] = $request;
                if ($this->disconnect) { throw new \RuntimeException('private transport detail'); }
                return new TransportResponse($this->status,['Content-Type'=>[$this->type],'Retry-After'=>['17']],$this->body);
            }
        };
        $service = new $class(new Client(new Configuration('synthetic-token'), $transport));
        try { return [$service->$method($input), $transport]; }
        catch (WistiaException $e) {
            self::assertCount(1,$transport->requests);
            self::assertNull($e->getPrevious());
            self::assertStringNotContainsString('private', (string) $e);
            self::assertStringNotContainsString('synthetic-token', (string) $e);
            self::assertFalse($e->remoteOutcomeUncertain);
            throw $e;
        }
    }
    public function testExactEndpointContract(): void
    {
        [,,,$path] = MediaAnalyticsCases::all()[static::OPERATION];
        [$result,$transport]=$this->invoke(); $request=$transport->requests[0];
        self::assertInstanceOf(Result::class,$result); self::assertSame(200,$result->status);
        self::assertSame('GET',$request->method); self::assertSame('https://api.wistia.com/modern'.$path,$request->url);
        self::assertSame('Bearer synthetic-token',$request->headers['Authorization']);
        self::assertSame('2026-09',$request->headers['X-Wistia-API-Version']);
        self::assertNull($request->body); self::assertArrayNotHasKey('Content-Type',$request->headers);
        self::assertTrue(property_exists($result, 'data') || property_exists($result, 'media_hashed_ids'));
        self::assertStringNotContainsString('private',print_r($result,true)); self::assertCount(1,$transport->requests);
    }
    #[DataProvider('failures')]
    public function testConsistentErrors(int $status, string $exception): void
    {
        try { $this->invoke($status, 'private response body'); self::fail('Expected failure'); }
        catch (WistiaException $e) {
            self::assertInstanceOf($exception,$e); self::assertSame($status,$e->status);
            self::assertSame('17',$e->rateLimit->retryAfter);
        }
    }
    public static function failures(): array
    {
        return [[400,ApiException::class],[401,AuthenticationException::class],[403,PermissionException::class],[404,ApiException::class],
            [422,ApiException::class],[429,RateLimitException::class],[500,ApiException::class],[503,ApiException::class],[408,ApiException::class],[302,ApiException::class]];
    }
    public function testTransportFailureIsSingleAttempt(): void
    {
        $this->expectException(TransportException::class); $this->invoke(disconnect:true);
    }
    public function testUnexpectedSuccessStatusIsNotAccepted(): void
    {
        $this->expectException(DecodeException::class); $this->invoke(201);
    }
    #[DataProvider('invalidBodies')]
    public function testMalformedResponseIsNotSuccess(#[\SensitiveParameter] string $body): void
    {
        $this->expectException(DecodeException::class); $this->invoke(body:$body);
    }
    public static function invalidBodies(): array { return [['{"private":'], ['null'], ['{"private":"data"}'], ['42']]; }
    public function testContentTypeIsChecked(): void
    {
        $this->expectException(DecodeException::class); $this->invoke(type:'text/html');
    }
}
