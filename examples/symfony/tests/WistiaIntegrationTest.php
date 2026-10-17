<?php
declare(strict_types=1);

namespace App\Tests;

use Automattic\Wistia\{Client, Configuration, DataMediaService, WebhookParser};
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class WistiaIntegrationTest extends WebTestCase
{
    private function fakeClient(int $status = 200, string $body = '[]', bool $fail = false): Transport
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
        // Replace only the client before the real service/command is resolved.
        static::getContainer()->set(Client::class,
            new Client(static::getContainer()->get(Configuration::class), $transport));
        return $transport;
    }

    public function testProductionClientBindingResolvesWithoutSending(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        self::assertInstanceOf(Client::class, $container->get(Client::class));
        self::assertSame($container->get(Configuration::class), $container->get(Configuration::class));
        self::assertInstanceOf(DataMediaService::class, $container->get(DataMediaService::class));
        self::assertInstanceOf(WebhookParser::class, $container->get(WebhookParser::class));
    }

    public function testContainerAndDiscoveredCommandUseDocumentedServices(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $transport = $this->fakeClient(200, '[{"folder":null,"name":"private media"}]');
        self::assertInstanceOf(DataMediaService::class, $container->get(DataMediaService::class));
        $tester = new CommandTester((new Application(static::$kernel))->find('wistia:media'));
        self::assertSame(0, $tester->execute([]));
        self::assertSame("Media records on this page: 1\n", $tester->getDisplay());
        self::assertCount(1, $transport->requests);
        self::assertSame('GET', $transport->requests[0]->method);
        self::assertStringEndsWith('/medias?per_page=25', $transport->requests[0]->url);
    }

    public function testTypedAuthenticationFailureIsSanitizedAndNotRetried(): void
    {
        self::bootKernel();
        $transport = $this->fakeClient(401, 'private response body');
        $tester = new CommandTester((new Application(static::$kernel))->find('wistia:media'));
        self::assertSame(1, $tester->execute([]));
        self::assertSame("Wistia failure: Automattic\\Wistia\\Exception\\AuthenticationException; HTTP 401\n", $tester->getDisplay());
        self::assertCount(1, $transport->requests);
    }

    public function testTransportFailureIsSanitizedAndNotRetried(): void
    {
        self::bootKernel();
        $transport = $this->fakeClient(200, '', true);
        $tester = new CommandTester((new Application(static::$kernel))->find('wistia:media'));
        self::assertSame(1, $tester->execute([]));
        self::assertSame("Wistia failure: Automattic\\Wistia\\Exception\\TransportException; HTTP none\n", $tester->getDisplay());
        self::assertCount(1, $transport->requests);
    }

    private function sendBody(KernelBrowser $browser, string $body, ?string $signature): void
    {
        $headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'];
        if ($signature !== null) { $headers['HTTP_X_WISTIA_SIGNATURE'] = $signature; }
        $browser->request('POST', '/webhooks/wistia', [], [], $headers, $body);
    }

    public function testWebhookPreservesOriginalBytesAndRejectsInvalidEnvelopes(): void
    {
        $browser = static::createClient();
        // Keep the injected client between requests; an accidental Wistia call is synthetic.
        $browser->disableReboot();
        $transport = $this->fakeClient(500, 'unexpected request');
        $body = '{"hook":{"uuid":"fixture"},"events":[{"uuid":"event","type":"example","generated_at":"2024-03-30T15:53:15Z","payload":{"private":"body"}}]}';
        $signature = hash_hmac('sha256', $body, 'synthetic-webhook-secret');
        $this->sendBody($browser, $body, $signature);
        self::assertResponseIsSuccessful();
        self::assertSame(['verified_events' => 1, 'processed' => false], json_decode($browser->getResponse()->getContent(), true));
        self::assertInstanceOf(WebhookParser::class, static::getContainer()->get(WebhookParser::class));
        foreach ([[$body . "\n", $signature], [$body, null], [$body, 'malformed'],
            [$body, hash_hmac('sha256', $body, 'synthetic-api-token')], [$body, $signature . ',' . $signature]] as [$bytes, $received]) {
            $this->sendBody($browser, $bytes, $received);
            self::assertResponseStatusCodeSame(401);
            self::assertSame(['error' => 'Invalid signature'], json_decode($browser->getResponse()->getContent(), true));
        }
        $this->sendBody($browser, 'not json', hash_hmac('sha256', 'not json', 'synthetic-webhook-secret'));
        self::assertResponseStatusCodeSame(400);
        self::assertSame(['error' => 'Invalid envelope'], json_decode($browser->getResponse()->getContent(), true));
        $oversized = str_repeat('x', 1048577);
        $this->sendBody($browser, $oversized, hash_hmac('sha256', $oversized, 'synthetic-webhook-secret'));
        self::assertResponseStatusCodeSame(413);
        self::assertSame(['error' => 'Body too large'], json_decode($browser->getResponse()->getContent(), true));
        $browser->request('GET', '/webhooks/wistia');
        self::assertResponseStatusCodeSame(405);
        self::assertCount(0, $transport->requests);
    }
}
