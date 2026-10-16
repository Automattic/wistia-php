<?php
declare(strict_types=1);

namespace Automattic\Wistia\Http;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\{CurlFactory, CurlFactoryInterface, EasyHandle};
use GuzzleHttp\Promise\{PromiseInterface, RejectedPromise};
use GuzzleHttp\Psr7\PumpStream;
use Psr\Http\Message\RequestInterface;

/** @internal Synchronous Guzzle handler: neither native rewinds nor factory redispatch are allowed. */
final class SingleAttemptCurlHandler
{
    private CurlFactoryInterface $factory;
    private \Closure $execute;

    /** The executor seam permits offline transfer completion tests without curl_exec/network I/O. */
    public function __construct(?CurlFactoryInterface $factory = null, ?\Closure $execute = null)
    {
        $this->factory = $factory ?? new CurlFactory(0);
        $this->execute = $execute ?? static function (#[\SensitiveParameter] EasyHandle $easy): void {
            curl_exec($easy->handle);
            $easy->errno = curl_errno($easy->handle);
        };
    }

    public function __invoke(#[\SensitiveParameter] RequestInterface $request, #[\SensitiveParameter] array $options): PromiseInterface
    {
        $body = $request->getBody();
        $size = $body->getSize();
        // Unknown size forces Guzzle's streaming path even below 1MB; PumpStream cannot rewind.
        $stream = new PumpStream(static function (int $length) use ($body): string|false {
            $data = $body->read($length);
            return $data === '' ? false : $data;
        });
        $request = $request->withBody($stream)->withProtocolVersion('1.1')->withoutHeader('Expect');
        if ($size !== null) {
            $request = $request->withoutHeader('Transfer-Encoding')->withHeader('Content-Length', (string) $size);
        }
        $options['version'] = '1.1';
        $options['expect'] = false;
        $options['auth'] = null;
        $options['proxy'] = '';
        $options['curl'] = [
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_HTTPAUTH => CURLAUTH_NONE,
        ];
        $easy = $this->factory->create($request, $options);
        try {
            ($this->execute)($easy);
        } catch (\Throwable $e) {
            $this->factory->release($easy);
            throw $e;
        }
        // Public finish retains Guzzle's response/error handling, but never dispatches another transfer.
        return CurlFactory::finish(
            static fn (#[\SensitiveParameter] RequestInterface $request, #[\SensitiveParameter] array $options): PromiseInterface =>
                new RejectedPromise(new RequestException('Automatic replay is disabled.', $request)),
            $easy,
            $this->factory
        );
    }

    public function __debugInfo(): array { return []; }
}
