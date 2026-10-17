<?php
declare(strict_types=1);

namespace Tests\Feature;

use Automattic\Wistia\{Client, Configuration, DataMediaService, WebhookParser};
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class WistiaIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'wistia.api_token' => 'synthetic-api-token',
            'wistia.webhook_secret' => 'synthetic-webhook-secret',
            // Fixture-only encryption key for the ordinary web-route CSRF control.
            'app.key' => 'base64:' . base64_encode(str_repeat('x', 32)),
        ]);
    }

    private function transport(int $status, string $body, bool $fail = false): Transport
    {
        $transport = new class($status, $body, $fail) implements Transport {
            public array $requests = [];
            public function __construct(private int $status, private string $body, private bool $fail) {}
            public function send(TransportRequest $request): TransportResponse
            {
                $this->requests[] = $request;
                if ($this->fail) { throw new \RuntimeException('private transport detail'); }
                return new TransportResponse($this->status, ['Content-Type' => ['application/json']], $this->body);
            }
        };
        // Replace only the HTTP client. Configuration, media service, provider,
        // command discovery and route/controller wiring remain the real example.
        $this->app->instance(Client::class, new Client($this->app->make(Configuration::class), $transport));
        return $transport;
    }

    public function testContainerAndArtisanReadUseDocumentedWiring(): void
    {
        self::assertSame($this->app->make(Configuration::class), $this->app->make(Configuration::class));
        self::assertSame($this->app->make(Client::class), $this->app->make(Client::class));
        self::assertInstanceOf(WebhookParser::class, $this->app->make(WebhookParser::class));
        $transport = $this->transport(200, '[{"folder":null,"name":"private media"}]');
        self::assertInstanceOf(DataMediaService::class, $this->app->make(DataMediaService::class));
        $this->artisan('wistia:media')->expectsOutput('Media records on this page: 1')->assertSuccessful();
        self::assertCount(1, $transport->requests);
        self::assertSame('GET', $transport->requests[0]->method);
        self::assertStringEndsWith('/medias?per_page=25', $transport->requests[0]->url);
    }

    public function testCommandHandlesTypedFailuresWithoutReplay(): void
    {
        $transport = $this->transport(401, 'private response');
        $this->artisan('wistia:media')
            ->expectsOutput('Wistia failure: Automattic\\Wistia\\Exception\\AuthenticationException; HTTP 401')
            ->assertFailed();
        self::assertCount(1, $transport->requests);
    }

    public function testCommandTransportFailureIsSanitized(): void
    {
        $transport = $this->transport(200, '', true);
        $this->artisan('wistia:media')
            ->expectsOutput('Wistia failure: Automattic\\Wistia\\Exception\\TransportException; HTTP none')
            ->assertFailed();
        self::assertCount(1, $transport->requests);
    }

    private function sendBody(string $body, ?string $signature): \Illuminate\Testing\TestResponse
    {
        $headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'];
        if ($signature !== null) { $headers['HTTP_X_WISTIA_SIGNATURE'] = $signature; }
        return $this->call('POST', '/webhooks/wistia', [], [], [], $headers, $body);
    }

    public function testWebhookOriginalBytesAndFailurePaths(): void
    {
        // Any accidental Wistia call would fail, never use the default transport.
        $transport = $this->transport(500, 'unexpected call');
        $body = '{"hook":{"uuid":"fixture"},"events":[{"uuid":"event","type":"example","generated_at":"2024-03-30T15:53:15Z","payload":{"private":"fixture body"}}]}';
        $signature = hash_hmac('sha256', $body, 'synthetic-webhook-secret');
        $this->sendBody($body, $signature)->assertOk()->assertExactJson(['verified_events' => 1, 'processed' => false]);
        $this->sendBody($body . "\n", $signature)->assertUnauthorized()->assertExactJson(['error' => 'Invalid signature']);
        $this->sendBody($body, null)->assertUnauthorized();
        $this->sendBody($body, 'malformed')->assertUnauthorized();
        $this->sendBody($body, hash_hmac('sha256', $body, 'synthetic-api-token'))->assertUnauthorized();
        $this->sendBody($body, $signature . ',' . $signature)->assertUnauthorized();
        $this->sendBody('not json', hash_hmac('sha256', 'not json', 'synthetic-webhook-secret'))
            ->assertBadRequest()->assertExactJson(['error' => 'Invalid envelope']);
        $oversized = str_repeat('x', 1048577);
        $this->sendBody($oversized, hash_hmac('sha256', $oversized, 'synthetic-webhook-secret'))->assertStatus(413);
        self::assertCount(0, $transport->requests);
    }

    public function testWebhookWorksWithoutCsrfWhileOrdinaryWebRoutesRetainProtection(): void
    {
        $this->transport(500, 'unexpected call');
        // Laravel skips CSRF in testing by default. Explicitly leave testing mode
        // for these requests so this assertion exercises the actual middleware.
        $this->app->instance('env', 'local');
        config(['session.driver' => 'array']);
        Route::middleware('web')->post('/csrf-control', static fn() => response('ok'));
        $this->post('/csrf-control')->assertStatus(419);
        $body = '{"hook":{"uuid":"fixture"},"events":[]}';
        $this->sendBody($body, hash_hmac('sha256', $body, 'synthetic-webhook-secret'))->assertOk();
        $this->get('/webhooks/wistia')->assertStatus(405);
    }
}
