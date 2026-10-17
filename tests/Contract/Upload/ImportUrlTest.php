<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Contract\Upload;

use Automattic\Wistia\{Client, Configuration, UploadService};
use Automattic\Wistia\Http\TransportResponse;
use Automattic\Wistia\Request\Upload\ImportUrlRequest;
use Automattic\Wistia\Upload\UploadOptions;
use Automattic\Wistia\Tests\Offline\RecordingTransport;
use PHPUnit\Framework\TestCase;

final class ImportUrlTest extends TestCase
{
    public function testUrlImportPreservesSignedUrlAndExactOptionalFields(): void
    {
        self::assertTrue(method_exists(UploadService::class, 'importUrl'), 'URL import must be explicitly supported.');
        $url = 'https://example.invalid/video.mp4?signature=a%2Fb%2Bc&expires=123&part=1&part=2';
        $transport = new RecordingTransport(new TransportResponse(200, ['Content-Type' => ['application/json']], '{"id":9,"hashed_id":"import123","progress":0}'));
        $service = new UploadService(new Client(new Configuration('fixture-secret'), $transport));
        $result = $service->importUrl(new ImportUrlRequest($url, new UploadOptions(project_id: 'folder123', name: 'A & B', description: '', contact_id: 7), low_priority: false));
        self::assertSame(9, $result->data->id);
        self::assertSame(0.0, $result->data->progress);
        $request = $transport->requests[0];
        self::assertSame('https://upload.wistia.com/', $request->url);
        self::assertSame('POST', $request->method);
        self::assertSame('application/x-www-form-urlencoded', $request->headers['Content-Type']);
        parse_str($request->body, $fields);
        self::assertSame(['url' => $url, 'project_id' => 'folder123', 'name' => 'A & B', 'description' => '', 'contact_id' => '7', 'low_priority' => 'false'], $fields);
        self::assertStringNotContainsString($url, print_r(new ImportUrlRequest($url), true));
    }
}
