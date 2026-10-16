<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\{Client, Configuration};
use Automattic\Wistia\Encoding\Body;
use Automattic\Wistia\Exception\TransportException;
use Automattic\Wistia\Http\{ApiFamily, GuzzleTransport, HttpMethod, JsonDecoder, Request, RequestSafety, SingleAttemptCurlHandler};
use GuzzleHttp\{Client as GuzzleClient, HandlerStack};
use GuzzleHttp\Handler\{CurlFactory, CurlFactoryInterface, EasyHandle};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class SingleAttemptCurlHandlerTest extends TestCase
{
    public function testDefaultTransportWiresSingleAttemptHandler(): void
    {
        $transport = new GuzzleTransport();
        $client = (new \ReflectionProperty($transport, 'client'))->getValue($transport);
        $stack = $client->getConfig('handler');
        self::assertInstanceOf(HandlerStack::class, $stack);
        self::assertInstanceOf(SingleAttemptCurlHandler::class, (new \ReflectionProperty($stack, 'handler'))->getValue($stack));
    }

    public function testRealFactoryAppliesNativeReplayPreventionAndTimeouts(): void
    {
        $process = proc_open([PHP_BINARY, __DIR__ . '/fixtures/native-curl-options.php'],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        self::assertIsResource($process);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        self::assertSame(0, proc_close($process), $stderr);
        self::assertSame('', $stderr);
        $results = json_decode($stdout, true, flags: JSON_THROW_ON_ERROR);
        self::assertCount(2, $results);
        foreach ($results as $size => $checks) {
            self::assertGreaterThanOrEqual(16, count($checks));
            foreach ($checks as $name => $passed) {
                self::assertTrue($passed, $size . ': ' . $name);
            }
        }
    }

    public function testNativeSuccessfulResponseIsDecodedWithoutRedispatch(): void
    {
        $dispatches = 0;
        $sent = null;
        $handler = new SingleAttemptCurlHandler(null, static function (#[\SensitiveParameter] EasyHandle $easy) use (&$dispatches, &$sent): void {
            ++$dispatches;
            $sent = $easy->request->getBody()->getContents();
            $easy->response = new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], '{"ok":true}');
        });
        $client = new Client(new Configuration('fixture-secret'), new GuzzleTransport(new GuzzleClient(['handler' => HandlerStack::create($handler)])));
        $result = $client->execute(new Request(ApiFamily::Upload, HttpMethod::Post, '/', RequestSafety::MayChangeRemoteState, [200], body: Body::json(['enabled' => false, 'count' => 0])), new JsonDecoder());
        self::assertSame(1, $dispatches);
        self::assertSame('{"enabled":false,"count":0}', $sent);
        self::assertSame(200, $result->status);
        self::assertTrue($result->result->value->ok);
    }

    public static function failedTransfers(): iterable
    {
        yield 'small consumed POST failed rewind' => [65, 32];
        yield 'large consumed POST failed rewind' => [65, 1000001];
        yield 'no response success legacy recovery' => [0, 32];
        yield 'empty POST failed rewind' => [65, 0];
    }

    #[DataProvider('failedTransfers')]
    public function testNativeFailureNeverRedispatchesAndIsSanitized(int $errno, int $size): void
    {
        $factory = new class implements CurlFactoryInterface {
            public int $creates = 0;
            public int $releases = 0;
            public array $options = [];
            public ?RequestInterface $request = null;
            private CurlFactory $inner;
            public function __construct() { $this->inner = new CurlFactory(0); }
            public function create(#[\SensitiveParameter] RequestInterface $request, #[\SensitiveParameter] array $options): EasyHandle
            {
                ++$this->creates;
                $this->options = $options;
                $this->request = $request;
                return $this->inner->create($request, $options);
            }
            public function release(EasyHandle $easy): void { ++$this->releases; $this->inner->release($easy); }
        };
        $dispatches = 0;
        $native = [];
        $handler = new SingleAttemptCurlHandler($factory, static function (#[\SensitiveParameter] EasyHandle $easy) use (&$dispatches, &$native, $errno): void {
            ++$dispatches;
            // Synthetic native completion: consume the actual factory request, never call curl_exec.
            // Assertions stay outside Client's catch-all boundary so a failed test cannot look like a transport failure.
            $body = $easy->request->getBody();
            $native = ['size' => $body->getSize(), 'seekable' => $body->isSeekable(), 'response' => $easy->response];
            $native['consumed'] = strlen($body->read(8));
            $native['rewindFailed'] = false;
            try { $body->rewind(); }
            catch (\RuntimeException) { $native['rewindFailed'] = true; }
            $easy->errno = $errno;
        });
        $client = new Client(new Configuration('fixture-secret', timeout: 7.5, connectTimeout: 2.5), new GuzzleTransport(new GuzzleClient(['handler' => HandlerStack::create($handler)])));
        $body = $size === 0 ? null : Body::json(['secret' => 'fixture-secret', 'data' => str_repeat('x', $size)]);
        try {
            $client->execute(new Request(ApiFamily::Upload, HttpMethod::Post, '/', RequestSafety::MayChangeRemoteState, [200], body: $body), new JsonDecoder());
            self::fail('Failed native transfer must surface.');
        } catch (TransportException $e) {
            self::assertTrue($e->remoteOutcomeUncertain);
            ExceptionAssertions::assertSanitized($e);
        }
        self::assertNull($native['size']);
        self::assertFalse($native['seekable']);
        self::assertNull($native['response']);
        self::assertTrue($native['rewindFailed']);
        self::assertSame($body === null ? 0 : 8, $native['consumed']);
        self::assertSame($body === null ? '0' : (string) strlen($body->contents()), $factory->request->getHeaderLine('Content-Length'));
        self::assertSame('Bearer fixture-secret', $factory->request->getHeaderLine('Authorization'));
        self::assertSame(1, $dispatches);
        self::assertSame(1, $factory->creates);
        self::assertSame(1, $factory->releases);
        self::assertSame(7.5, $factory->options['timeout']);
        self::assertSame(2.5, $factory->options['connect_timeout']);
        self::assertTrue($factory->options['verify']);
        self::assertFalse($factory->options['allow_redirects']);
        self::assertFalse($factory->options['http_errors']);
        self::assertFalse($factory->options['debug']);
        self::assertFalse($factory->options['cookies']);
        self::assertSame('1.1', $factory->request->getProtocolVersion());
        self::assertSame('', $factory->request->getHeaderLine('Expect'));
        self::assertFalse($factory->options['expect']);
        self::assertNull($factory->options['auth']);
        self::assertSame('', $factory->options['proxy']);
        self::assertSame([
            CURLOPT_FRESH_CONNECT => true, CURLOPT_FORBID_REUSE => true,
            CURLOPT_HTTPAUTH => CURLAUTH_NONE,
        ], $factory->options['curl']);
    }
}
