<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\{Client, Configuration};
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Jobs\PollingRuntime;
use PHPUnit\Framework\TestCase;

final class ExamplesTest extends TestCase
{
    private function execute(string $name, array $args = [], array $responses = [], array $env = []): array
    {
        $transport = new class($responses) implements Transport {
            public array $requests = [];
            public function __construct(private array $responses) {}
            public function send(TransportRequest $request): TransportResponse
            {
                $this->requests[] = $request;
                if ($this->responses === []) { throw new \RuntimeException('No HTTP permitted'); }
                $next = array_shift($this->responses);
                if ($next instanceof \Throwable) { throw $next; }
                return new TransportResponse($next[0], ['Content-Type' => ['application/json']], $next[1]);
            }
        };
        $clock = new class implements PollingRuntime {
            public float $time = 0.0;
            public array $waits = [];
            public function now(): float { return $this->time; }
            public function wait(float $seconds): void { $this->waits[] = $seconds; $this->time += $seconds; }
        };
        $entry = require dirname(__DIR__, 2) . '/examples/plain-php/' . $name . '.php';
        $lines = [];
        $code = $entry($args, static fn($key) => $env[$key] ?? false,
            static function ($line) use (&$lines) { $lines[] = $line; },
            new Client(new Configuration('synthetic'), $transport), $clock);
        return [$code, implode("\n", $lines), $transport, $clock];
    }

    public function testListingOnePageAndBoundedTraversal(): void
    {
        [$code, $output, $transport] = $this->execute('list-media', [], [[200, '[{"folder":null}]'], [200, '[{"folder":null}]']]);
        self::assertSame(0, $code);
        self::assertStringContainsString('Single page records: 1', $output);
        self::assertStringContainsString('Bounded traversal records: 1', $output);
        self::assertCount(2, $transport->requests);
        self::assertSame('GET', $transport->requests[0]->method);
        $full = json_encode(array_fill(0, 25, ['folder' => null]));
        [$code, $output, $transport] = $this->execute('list-media', [], array_fill(0, 4, [200, $full]));
        self::assertSame(4, $code);
        self::assertCount(4, $transport->requests); // one page plus exactly three traversal pages
        self::assertStringContainsString('incomplete', $output);
    }

    public function testUploadAndImportActualEntryPointsAndUncertainWrites(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'wistia-example-');
        file_put_contents($path, 'synthetic video bytes');
        try {
            [$code, $output, $transport] = $this->execute('upload-file', ['--execute', $path], [[200, '{"hashed_id":"abc","progress":0}']]);
            self::assertSame(0, $code);
            self::assertStringContainsString('UploadFileResult accepted', $output);
            self::assertStringContainsString('not confirmed', $output);
            self::assertSame('POST', $transport->requests[0]->method);
            [$code, $output, $transport] = $this->execute('upload-file', ['--execute', $path], [new \RuntimeException('private source')]);
            self::assertSame(1, $code);
            self::assertStringContainsString('outcome uncertain', $output);
            self::assertStringNotContainsString('private source', $output);
            self::assertCount(1, $transport->requests);
        } finally { unlink($path); }
        $url = 'https://example.com/video.mp4?signature=private-signed-grant';
        [$code, $output, $transport] = $this->execute('import-url', ['--execute'], [[200, '{"hashed_id":"abc"}']], ['WISTIA_SOURCE_URL' => $url]);
        self::assertSame(0, $code);
        self::assertStringContainsString('ImportUrlResult accepted', $output);
        self::assertStringNotContainsString($url, $output);
        self::assertCount(1, $transport->requests);
        [$code, $output, $transport] = $this->execute('import-url', ['--execute'], [[503, 'private body']], ['WISTIA_SOURCE_URL' => $url]);
        self::assertSame(1, $code);
        self::assertStringContainsString('outcome uncertain', $output);
        self::assertStringNotContainsString('private', $output);
        self::assertCount(1, $transport->requests);
    }

    public function testInvalidInputAndTypedReadFailures(): void
    {
        foreach ([['upload-file', []], ['upload-file', ['--execute', '/nonexistent']],
            ['import-url', ['--execute']], ['import-url', ['--execute'], ['WISTIA_SOURCE_URL' => 'invalid']],
            ['poll-job', ['job', '0']], ['webhook', ['/nonexistent']], ['list-media', ['extra']]] as $case) {
            [$code, $output, $transport] = $this->execute($case[0], $case[1], [], $case[2] ?? []);
            self::assertSame(2, $code);
            self::assertStringContainsString('Local input', $output);
            self::assertCount(0, $transport->requests);
        }
        foreach ([401, 403, 429] as $status) {
            [$code, $output, $transport] = $this->execute('list-media', [], [[$status, 'private body']]);
            self::assertSame(1, $code);
            self::assertStringContainsString('Library failure:', $output);
            self::assertStringNotContainsString('private', $output);
            self::assertCount(1, $transport->requests);
        }
    }

    public function testFinitePollingCompletionFailurePartialAndExhaustion(): void
    {
        $job = static fn($state, $object = '') => [200, '{"background_job_status":{"id":42,"hashed_id":"job","status":"' . $state . '"' . $object . '}}'];
        foreach ([['finished', '', 0], ['failed', '', 3], ['finished', ',"object":{"type":"bulk_operation","summary":{"total":2,"succeeded":1,"failed":1},"results":[{"position":0,"status":"succeeded"},{"position":1,"status":"failed","error":"private"}]}', 3], ['started', '', 4]] as [$state, $object, $expected]) {
            [$code, $output, $transport, $clock] = $this->execute('poll-job', ['job', '2'], [$job('queued'), $job($state, $object)]);
            self::assertSame($expected, $code);
            self::assertSame(['GET', 'GET'], array_column($transport->requests, 'method'));
            self::assertSame([1.0], $clock->waits);
            self::assertStringNotContainsString('private', $output);
            if ($object !== '') { self::assertStringContainsString('succeeded=1; failed=1', $output); }
        }
    }

    public function testOriginalWebhookBytesAndReceivedSignature(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'wistia-hook-');
        $body = '{"hook":{"uuid":"fixture"},"events":[{"uuid":"event","type":"example","generated_at":"2024-03-30T15:53:15Z","payload":{"private":"body"}}]}';
        file_put_contents($path, $body);
        $env = ['WISTIA_WEBHOOK_SECRET' => 'fixture-only', 'WISTIA_WEBHOOK_SIGNATURE' => hash_hmac('sha256', $body, 'fixture-only')];
        try {
            [$code, $output, $transport] = $this->execute('webhook', [$path], [], $env);
            self::assertSame(0, $code);
            self::assertStringContainsString('Verified events: 1', $output);
            self::assertStringNotContainsString('private', $output);
            self::assertCount(0, $transport->requests);
            file_put_contents($path, $body . "\n");
            [$code, $output] = $this->execute('webhook', [$path], [], $env);
            self::assertSame(1, $code);
            self::assertStringContainsString('VerificationException', $output);
            file_put_contents($path, 'not json');
            $env['WISTIA_WEBHOOK_SIGNATURE'] = hash_hmac('sha256', 'not json', 'fixture-only');
            [$code, $output] = $this->execute('webhook', [$path], [], $env);
            self::assertSame(1, $code);
            self::assertStringContainsString('ParsingException', $output);
        } finally { unlink($path); }
    }
}
