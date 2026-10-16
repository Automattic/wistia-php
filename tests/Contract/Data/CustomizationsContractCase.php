<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Data;

use Automattic\Wistia\{Client, Configuration, DataCustomizationsService};
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Exception\{ApiException, AuthenticationException, PermissionException, RateLimitException, DecodeException, TransportException};
use PHPUnit\Framework\TestCase;

abstract class CustomizationsContractCase extends TestCase
{
    abstract protected function operation(): string;
    protected function runCase(?int $status = null, ?string $body = null, bool $disconnect = false): array
    {
        [$method, $verb, $path, $success, $input, $expectedBody, $fixture] = CustomizationsCases::all()[$this->operation()];
        $transport = new class($status ?? $success, $body ?? $fixture, $disconnect) implements Transport {
            public array $requests = [];
            public function __construct(private int $status, private string $body, private bool $disconnect) {}
            public function send(TransportRequest $request): TransportResponse
            {
                $this->requests[] = $request;
                if ($this->disconnect) { throw new \RuntimeException('synthetic-private'); }
                return new TransportResponse($this->status, ['Content-Type' => ['application/json'], 'Retry-After' => ['7']], $this->body);
            }
        };
        $class = 'Automattic\\Wistia\\Request\\Data\\' . $this->operation() . 'Request';
        $request = $input === null ? new $class('media/ +') : new $class('media/ +', $input);
        try {
            $result = (new DataCustomizationsService(new Client(new Configuration('synthetic-token'), $transport)))->$method($request);
            return [$result, $transport, $verb, $path, $expectedBody, $fixture];
        } catch (\Automattic\Wistia\Exception\WistiaException $e) {
            self::assertCount(1, $transport->requests);
            self::assertStringNotContainsString('synthetic-private', (string) $e);
            self::assertStringNotContainsString('synthetic-token', (string) $e);
            self::assertNull($e->getPrevious());
            throw $e;
        }
    }
    public function testExactContract(): void
    {
        [$result, $transport, $verb, $path, $expectedBody, $fixture] = $this->runCase();
        self::assertCount(1, $transport->requests);
        $sent = $transport->requests[0];
        self::assertSame($verb, $sent->method);
        self::assertSame('https://api.wistia.com/modern' . $path, $sent->url);
        self::assertSame('Bearer synthetic-token', $sent->headers['Authorization']);
        self::assertSame('2026-09', $sent->headers['X-Wistia-API-Version']);
        self::assertSame($expectedBody, $sent->body);
        if ($expectedBody !== null) { self::assertSame('application/json', $sent->headers['Content-Type']); }
        self::assertSame(CustomizationsCases::all()[$this->operation()][3], $result->status);
        if ($fixture !== '') {
            self::assertSame([], $result->__debugInfo());
            self::assertSame([], $result->data->__debugInfo());
            self::assertSame(array_keys(get_object_vars(json_decode($fixture))), $result->data->presentFields);
        }
    }
    public function testFailuresAreSanitizedAndNotReplayed(): void
    {
        foreach ([401 => AuthenticationException::class, 403 => PermissionException::class, 404 => ApiException::class,
            422 => ApiException::class, 429 => RateLimitException::class, 500 => ApiException::class, 302 => ApiException::class] as $status => $class) {
            try { $this->runCase($status, '{"secret":"synthetic-private"}'); self::fail('Expected failure'); }
            catch (\Automattic\Wistia\Exception\WistiaException $e) {
                self::assertInstanceOf($class, $e);
                self::assertSame($status, $e->status);
                self::assertSame('7', $e->rateLimit->retryAfter);
                self::assertSame($this->isWrite() && ($status >= 500 || $status === 302), $e->remoteOutcomeUncertain);
            }
        }
        try { $this->runCase(disconnect: true); self::fail('Expected transport failure'); }
        catch (TransportException $e) { self::assertSame($this->isWrite(), $e->remoteOutcomeUncertain); }
    }
    public function testMalformedResponseAndWrongStatus(): void
    {
        $bodies = $this->operation() === 'DeleteCustomizations' ? [] : ['not json', '[]', '{"unknown":true}', '{"plugin":false}'];
        foreach ($bodies as $body) {
            try { $this->runCase(body: $body); self::fail('Expected decoding failure'); }
            catch (DecodeException $e) { self::assertSame($this->isWrite(), $e->remoteOutcomeUncertain); }
        }
        try { $this->runCase(202); self::fail('Expected unexpected status failure'); }
        catch (DecodeException $e) { self::assertSame($this->isWrite(), $e->remoteOutcomeUncertain); }
    }
    private function isWrite(): bool { return CustomizationsCases::all()[$this->operation()][1] !== 'GET'; }
}
