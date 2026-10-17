<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Contract\Upload;

use Automattic\Wistia\{Client, Configuration, UploadService};
use Automattic\Wistia\Http\TransportResponse;
use Automattic\Wistia\Request\Upload\UploadFileRequest;
use Automattic\Wistia\Upload\LocalFile;
use Automattic\Wistia\Tests\Offline\RecordingTransport;
use PHPUnit\Framework\TestCase;

final class UploadFileTest extends TestCase
{
    public function testFileUploadUsesMultipartAndTypedUploadResult(): void
    {
        self::assertTrue(class_exists(UploadService::class), 'The explicit Upload service must be available.');
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, 'synthetic-video-bytes'); rewind($stream);
        $transport = new RecordingTransport(new TransportResponse(200, ['Content-Type' => ['application/json']],
            '{"id":7,"hashed_id":"fixture123","name":"test.mp4","type":"Video","progress":0.0,"duration":1.25,"thumbnail":{"url":"https://example.invalid/test.jpg","width":160,"height":90},"status":"queued"}'));
        try {
            $service = new UploadService(new Client(new Configuration('fixture-secret'), $transport));
            $result = $service->uploadFile(new UploadFileRequest(LocalFile::fromStream($stream, 'test.mp4', 'video/mp4')));
            self::assertSame(200, $result->status);
            self::assertSame('fixture123', $result->data->hashed_id);
            self::assertSame(0.0, $result->data->progress);
            self::assertSame(160, $result->data->thumbnail->width);
            self::assertSame('queued', $result->data->status);
            self::assertCount(1, $transport->requests);
            $request = $transport->requests[0];
            self::assertSame('POST', $request->method);
            self::assertSame('https://upload.wistia.com/', $request->url);
            self::assertSame('Bearer fixture-secret', $request->headers['Authorization']);
            self::assertArrayNotHasKey('X-Wistia-API-Version', $request->headers);
            self::assertStringStartsWith('multipart/form-data; boundary=', $request->headers['Content-Type']);
            $body = (string) $request->body;
            self::assertStringContainsString('name="file"; filename="test.mp4"', $body);
            self::assertStringContainsString('Content-Type: video/mp4', $body);
            self::assertStringContainsString('synthetic-video-bytes', $body);
            self::assertIsResource($stream);
        } finally { fclose($stream); }
    }
}
