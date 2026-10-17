<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Contract\Upload;

use Automattic\Wistia\{Client, Configuration, UploadService};
use Automattic\Wistia\Http\TransportResponse;
use Automattic\Wistia\Request\Upload\ImportThumbnailUrlRequest;
use Automattic\Wistia\Result\Upload\ImportThumbnailUrlResult;
use Automattic\Wistia\Upload\LocalFile;
use Automattic\Wistia\Tests\Offline\RecordingTransport;
use PHPUnit\Framework\TestCase;

final class ImportThumbnailUrlTest extends TestCase
{
    // Synthetic fixture derived from the approved live probe: 201, JSON content type, zero-byte body.
    public function testObservedWorkflowContract(): void
    {
        self::assertTrue(method_exists(UploadService::class, 'importThumbnailUrl'), 'Explicit workflow must be available.');
        $transport = new RecordingTransport(new TransportResponse(201, ['Content-Type' => ['application/json;charset=utf-8']], ''));
        $service = new UploadService(new Client(new Configuration('fixture-secret'), $transport));
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, 'synthetic-image-bytes'); rewind($stream);
        try {
            $result = $service->importThumbnailUrl(new ImportThumbnailUrlRequest('media123', 'https://example.invalid/thumb.png?signature=a%2Fb&x=1'));
            self::assertInstanceOf(ImportThumbnailUrlResult::class, $result);
            self::assertSame(201, $result->status);
            self::assertCount(1, $transport->requests);
            $wire = $transport->requests[0];
            self::assertSame('https://upload.wistia.com/thumbnail?media_id=media123', $wire->url);
            self::assertSame('POST', $wire->method);
            self::assertSame('Bearer fixture-secret', $wire->headers['Authorization']);
            self::assertArrayNotHasKey('X-Wistia-API-Version', $wire->headers);
            self::assertSame('application/x-www-form-urlencoded', $wire->headers['Content-Type']);
            parse_str($wire->body, $fields);
            self::assertSame(json_decode('{"url":"https://example.invalid/thumb.png?signature=a%2Fb&x=1"}', true), $fields);
            self::assertIsResource($stream);
        } finally { fclose($stream); }
    }
}
