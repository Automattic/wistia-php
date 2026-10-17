<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Contract\Upload;

use Automattic\Wistia\{Client, Configuration, UploadService};
use Automattic\Wistia\Http\TransportResponse;
use Automattic\Wistia\Request\Upload\ImportLocalizationAudioRequest;
use Automattic\Wistia\Result\Upload\ImportLocalizationAudioResult;
use Automattic\Wistia\Upload\LocalFile;
use Automattic\Wistia\Tests\Offline\RecordingTransport;
use PHPUnit\Framework\TestCase;

final class ImportLocalizationAudioTest extends TestCase
{
    // Synthetic fixture derived from the approved live probe: 201, JSON content type, zero-byte body.
    public function testObservedWorkflowContract(): void
    {
        self::assertTrue(method_exists(UploadService::class, 'importLocalizationAudio'), 'Explicit workflow must be available.');
        $transport = new RecordingTransport(new TransportResponse(201, ['Content-Type' => ['application/json;charset=utf-8']], ''));
        $service = new UploadService(new Client(new Configuration('fixture-secret'), $transport));
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, 'synthetic-image-bytes'); rewind($stream);
        try {
            $result = $service->importLocalizationAudio(new ImportLocalizationAudioRequest('media123', 'es-419', 'https://example.invalid/audio.mp3?signature=a%2Fb', overwrite_existing_localization: false));
            self::assertInstanceOf(ImportLocalizationAudioResult::class, $result);
            self::assertSame(201, $result->status);
            self::assertCount(1, $transport->requests);
            $wire = $transport->requests[0];
            self::assertSame('https://upload-v2.wistia.com/', $wire->url);
            self::assertSame('POST', $wire->method);
            self::assertSame('Bearer fixture-secret', $wire->headers['Authorization']);
            self::assertArrayNotHasKey('X-Wistia-API-Version', $wire->headers);
            self::assertSame('application/x-www-form-urlencoded', $wire->headers['Content-Type']);
            parse_str($wire->body, $fields);
            self::assertSame(json_decode('{"url":"https://example.invalid/audio.mp3?signature=a%2Fb","localization_language":"es-419","localization_media_id":"media123","overwrite_existing_localization":"false"}', true), $fields);
            self::assertIsResource($stream);
        } finally { fclose($stream); }
    }
}
