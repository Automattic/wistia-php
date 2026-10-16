<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Data;

use Automattic\Wistia\{Client, Configuration};
use Automattic\Wistia\Exception\{ApiException, AuthenticationException, DecodeException, PermissionException, RateLimitException, TransportException, WistiaException};
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Result\Result;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

abstract class FolderOperationContract extends TestCase
{
    protected const OPERATION = '';
    protected function invoke(?int $status = null, ?string $body = null, bool $disconnect = false, string $type = 'application/json'): array
    {
        [$class,$method,$input,$verb,$path,$expected,$success,$fixture] = FolderCases::all()[static::OPERATION];
        $transport = new class($status ?? $success, $body ?? $fixture, $disconnect, $type) implements Transport {
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
            self::assertCount(1,$transport->requests, 'Never replay writes or reads automatically.');
            self::assertNull($e->getPrevious());
            self::assertStringNotContainsString('private', $e->getMessage());
            self::assertStringNotContainsString('synthetic-token', $e->getMessage());
            throw $e;
        }
    }
    public function testExactEndpointContract(): void
    {
        [$class,$method,$input,$verb,$path,$expected,$success,$fixture] = FolderCases::all()[static::OPERATION];
        [$result,$transport]=$this->invoke(); $request=$transport->requests[0];
        self::assertInstanceOf(Result::class,$result); self::assertSame($success,$result->status);
        self::assertSame($verb,$request->method); self::assertSame('https://api.wistia.com/modern'.$path,$request->url);
        self::assertSame('Bearer synthetic-token',$request->headers['Authorization']);
        self::assertSame('2026-09',$request->headers['X-Wistia-API-Version']);
        if ($expected === null) { self::assertNull($request->body); self::assertArrayNotHasKey('Content-Type',$request->headers); }
        else { self::assertSame('application/json',$request->headers['Content-Type']); self::assertEquals(json_decode($expected),json_decode($request->body)); }
        if ($result->data !== null) { self::assertTrue(is_array($result->data) || is_object($result->data)); }
        self::assertStringNotContainsString('private',print_r($result,true)); self::assertCount(1,$transport->requests);
    }
    #[DataProvider('failures')]
    public function testConsistentErrors(int $status, string $exception): void
    {
        try { $this->invoke($status, 'private response body'); self::fail('Expected failure'); }
        catch (WistiaException $e) {
            self::assertInstanceOf($exception,$e); self::assertSame($status,$e->status);
            self::assertSame('17',$e->rateLimit->retryAfter);
            $isWrite=FolderCases::all()[static::OPERATION][3]!=='GET';
            self::assertSame($isWrite && ($status>=500 || $status===408 || $status===302),$e->remoteOutcomeUncertain);
        }
    }
    public static function failures(): array
    {
        return [[401,AuthenticationException::class],[403,PermissionException::class],[404,ApiException::class],
            [422,ApiException::class],[429,RateLimitException::class],[500,ApiException::class],[408,ApiException::class],[302,ApiException::class]];
    }
    public function testTransportFailureIsSingleAttempt(): void
    {
        try { $this->invoke(disconnect:true); self::fail('Expected transport failure'); }
        catch (TransportException $e) { self::assertSame(FolderCases::all()[static::OPERATION][3]!=='GET',$e->remoteOutcomeUncertain); }
    }
    public function testUnexpectedSuccessStatusIsNotAccepted(): void
    {
        $this->expectException(DecodeException::class); $this->invoke(299);
    }
    public function testMalformedResponseIsNotSuccess(): void
    {
        $this->expectException(DecodeException::class); $this->invoke(body:'{"private":');
    }
    public function testNullResponseIsNotSuccess(): void
    {
        $this->expectException(DecodeException::class); $this->invoke(body:'null');
    }
    public function testContentTypeIsChecked(): void
    {
        $this->expectException(DecodeException::class); $this->invoke(type:'text/html');
    }
}
