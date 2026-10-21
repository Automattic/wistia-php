<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use PHPUnit\Framework\TestCase;

final class ConsumerAutoloadTest extends TestCase
{
    public function testProvisionedComposerAutoloaderWorksOutsideTheRepositoryCwd(): void
    {
        // A consumer-process smoke check with existing dependencies, not a fresh Composer install.
        $autoload = var_export(dirname(__DIR__, 2) . '/vendor/autoload.php', true);
        $code = 'require ' . $autoload . ';' . <<<'PHP'

use Automattic\Wistia\{Client, Configuration, DataMediaService};
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Request\Data\ListMediaRequest;

if (!extension_loaded('curl') || !class_exists(GuzzleHttp\Client::class)) {
    throw new RuntimeException('Consumer platform/dependencies unavailable.');
}
$transport = new class implements Transport {
    public int $calls = 0;
    public function send(#[SensitiveParameter] TransportRequest $request): TransportResponse {
        if ($request->url !== 'https://api.wistia.com/modern/medias?per_page=25'
            || $request->headers['Authorization'] !== 'Bearer synthetic-consumer-token'
            || $request->headers['X-Wistia-API-Version'] !== '2026-09') {
            throw new RuntimeException('Consumer request contract mismatch.');
        }
        ++$this->calls;
        return new TransportResponse(200, ['Content-Type' => ['application/json']], '[{"folder":null}]');
    }
};
$result = (new DataMediaService(new Client(new Configuration('synthetic-consumer-token'), $transport)))
    ->listMedia(new ListMediaRequest(per_page: 25));
if ($transport->calls !== 1 || count($result->data) !== 1 || $result->status !== 200) {
    throw new RuntimeException('Consumer result mismatch.');
}
echo 'consumer-ok';
PHP;
        $process = proc_open([PHP_BINARY, '-d', 'allow_url_fopen=0', '-r', $code],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, sys_get_temp_dir(), []);
        self::assertIsResource($process);
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        $exit = proc_close($process);
        self::assertSame(0, $exit, $stderr);
        self::assertSame('', $stderr);
        self::assertSame('consumer-ok', $stdout);
    }
}
