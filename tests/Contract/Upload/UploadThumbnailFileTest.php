<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Contract\Upload;

use Automattic\Wistia\{Client, Configuration, UploadService};
use Automattic\Wistia\Http\TransportResponse;
use Automattic\Wistia\Request\Upload\UploadThumbnailFileRequest;
use Automattic\Wistia\Result\Upload\UploadThumbnailFileResult;
use Automattic\Wistia\Upload\LocalFile;
use Automattic\Wistia\Tests\Offline\RecordingTransport;
use PHPUnit\Framework\TestCase;

final class UploadThumbnailFileTest extends TestCase
{
    // Synthetic fixture derived from the approved live probe: 201, JSON content type, zero-byte body.
    public function testObservedWorkflowContract(): void
    {
        self::assertTrue(method_exists(UploadService::class, 'uploadThumbnailFile'), 'Explicit workflow must be available.');
        $transport = new RecordingTransport(new TransportResponse(201, ['Content-Type' => ['application/json;charset=utf-8']], ''));
        $service = new UploadService(new Client(new Configuration('fixture-secret'), $transport));
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, 'synthetic-image-bytes'); rewind($stream);
        try {
            $result = $service->uploadThumbnailFile(new UploadThumbnailFileRequest('media123', LocalFile::fromStream($stream, 'thumb.png', 'image/png')));
            self::assertInstanceOf(UploadThumbnailFileResult::class, $result);
            self::assertSame(201, $result->status);
            self::assertCount(1, $transport->requests);
            $wire = $transport->requests[0];
            self::assertSame('https://upload.wistia.com/thumbnail?media_id=media123', $wire->url);
            self::assertSame('POST', $wire->method);
            self::assertSame('Bearer fixture-secret', $wire->headers['Authorization']);
            self::assertArrayNotHasKey('X-Wistia-API-Version', $wire->headers);
            self::assertStringStartsWith('multipart/form-data; boundary=', $wire->headers['Content-Type']);
            self::assertStringContainsString('synthetic-image-bytes', (string) $wire->body);
            self::assertIsResource($stream);
        } finally { fclose($stream); }
    }
}
