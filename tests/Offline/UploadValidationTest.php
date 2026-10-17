<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\{Client, Configuration, UploadService};
use Automattic\Wistia\Http\TransportResponse;
use Automattic\Wistia\Request\Upload\{UploadFileRequest, ImportUrlRequest, ImportThumbnailUrlRequest, ImportLocalizationAudioRequest};
use Automattic\Wistia\Upload\{LocalFile, UploadOptions};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UploadValidationTest extends TestCase
{
    public static function invalidInputs(): iterable
    {
        yield 'missing-file' => ['missing-file'];
        yield 'directory' => ['directory'];
        yield 'wrapper' => ['wrapper'];
        yield 'empty-file' => ['empty-file'];
        yield 'deleted-file' => ['deleted-file'];
        yield 'closed-stream' => ['closed-stream'];
        yield 'exhausted-stream' => ['exhausted-stream'];
        yield 'write-only' => ['write-only'];
        yield 'filename-injection' => ['filename-injection'];
        yield 'content-type-injection' => ['content-type-injection'];
        yield 'invalid-url' => ['invalid-url'];
        yield 'missing-extension' => ['missing-extension'];
        yield 'non-image-extension' => ['non-image-extension'];
        yield 'invalid-language' => ['invalid-language'];
        yield 'invalid-media-id' => ['invalid-media-id'];
        yield 'long-name' => ['long-name'];
        yield 'invalid-utf8' => ['invalid-utf8'];
        yield 'negative-contact' => ['negative-contact'];
    }
    #[DataProvider('invalidInputs')]
    public function testDetectableInvalidInputsFailBeforeAnyRequest(string $case): void
    {
        $transport = new RecordingTransport(new TransportResponse(200, [], '{}'));
        $service = new UploadService(new Client(new Configuration('fixture-secret'), $transport));
        $path = tempnam(sys_get_temp_dir(), 'wistia-input-');
        file_put_contents($path, 'fixture-secret');
        $handle = fopen($path, $case === 'write-only' ? 'wb' : 'rb');
        try {
            try {
                switch ($case) {
                    case 'missing-file': LocalFile::fromPath($path . '-missing'); break;
                    case 'directory': LocalFile::fromPath(dirname($path)); break;
                    case 'wrapper': LocalFile::fromPath('https://example.invalid/fixture-secret.mp4'); break;
                    case 'empty-file': file_put_contents($path, ''); $service->uploadFile(new UploadFileRequest(LocalFile::fromPath($path))); break;
                    case 'deleted-file': $file = LocalFile::fromPath($path); unlink($path); $service->uploadFile(new UploadFileRequest($file)); break;
                    case 'closed-stream': $file = LocalFile::fromStream($handle, 'test.mp4'); fclose($handle); $service->uploadFile(new UploadFileRequest($file)); break;
                    case 'exhausted-stream': $file = LocalFile::fromStream($handle, 'test.mp4'); stream_get_contents($handle); $service->uploadFile(new UploadFileRequest($file)); break;
                    case 'write-only': LocalFile::fromStream($handle, 'test.mp4'); break;
                    case 'filename-injection': LocalFile::fromStream($handle, "test\r\nfixture-secret"); break;
                    case 'content-type-injection': LocalFile::fromStream($handle, 'test.mp4', "video/mp4\r\nfixture-secret"); break;
                    case 'invalid-url': $service->importUrl(new ImportUrlRequest('file:///fixture-secret.mp4')); break;
                    case 'missing-extension': $service->importUrl(new ImportUrlRequest('https://example.invalid/fixture-secret')); break;
                    case 'non-image-extension': $service->importThumbnailUrl(new ImportThumbnailUrlRequest('media123', 'https://example.invalid/fixture-secret.mp4')); break;
                    case 'invalid-language': $service->importLocalizationAudio(new ImportLocalizationAudioRequest('media123', 'en_US', 'https://example.invalid/fixture-secret.mp3')); break;
                    case 'invalid-media-id': new ImportThumbnailUrlRequest('../fixture-secret', 'https://example.invalid/image.png'); break;
                    case 'long-name': new UploadOptions(name: str_repeat('é', 256)); break;
                    case 'invalid-utf8': new UploadOptions(name: "\xfffixture-secret"); break;
                    case 'negative-contact': new UploadOptions(contact_id: -1); break;
                }
                self::fail('Expected local validation failure.');
            } catch (\InvalidArgumentException $e) {
                self::assertStringNotContainsString('fixture-secret', (string) $e);
                self::assertNull($e->getPrevious());
            }
            self::assertCount(0, $transport->requests);
        } finally { if (is_resource($handle)) { fclose($handle); } if (is_file($path)) { unlink($path); } }
    }

    public static function invalidLanguageTags(): iterable
    {
        foreach (['en-a', 'en-x', 'en-a-x-demo', 'en-u-ca-u-nu', 'de-1901-1901', 'i-invented', 'x', 'en--US', 'en_US', 'en-a-1', 'en-US-Latn', 'en-abc-def'] as $tag) { yield $tag => [$tag]; }
    }

    #[DataProvider('invalidLanguageTags')]
    public function testMalformedLanguageTagsFailLocallyWithoutDispatch(string $tag): void
    {
        $transport = new RecordingTransport(new TransportResponse(201, [], ''));
        $service = new UploadService(new Client(new Configuration('fixture-secret'), $transport));
        try {
            $service->importLocalizationAudio(new ImportLocalizationAudioRequest('media123', $tag, 'https://example.invalid/audio.mp3?signature=fixture-secret'));
            self::fail('Structurally invalid language tag must be rejected.');
        } catch (\InvalidArgumentException $e) {
            self::assertStringNotContainsString('fixture-secret', (string) $e);
            self::assertNull($e->getPrevious());
        }
        self::assertCount(0, $transport->requests);
    }

    public static function validLanguageTags(): iterable
    {
        foreach (['es-419', 'en', 'zh-Hant-TW', 'de-CH-1901', 'sl-rozaj-biske-1994', 'en-a-foo-b-bar-x-private', 'en-US-u-ca-gregory', 'x-private', 'i-klingon', 'en-GB-oed', 'zh-min-nan', 'EN-us-U-ca-gregory'] as $tag) { yield $tag => [$tag]; }
    }

    #[DataProvider('validLanguageTags')]
    public function testValidLanguageTagStructuresArePreserved(string $tag): void
    {
        $transport = new RecordingTransport(new TransportResponse(201, ['Content-Type' => ['application/json']], ''));
        $service = new UploadService(new Client(new Configuration('fixture-secret'), $transport));
        $result = $service->importLocalizationAudio(new ImportLocalizationAudioRequest('media123', $tag, 'https://example.invalid/audio.mp3'));
        self::assertSame(201, $result->status);
        parse_str($transport->requests[0]->body, $fields);
        self::assertSame($tag, $fields['localization_language']);
        self::assertCount(1, $transport->requests);
    }

    public function testUnicodeNameLimitAndOmission(): void
    {
        $options = new UploadOptions(name: str_repeat('é', 255), description: '0');
        self::assertSame(['name' => str_repeat('é', 255), 'description' => '0'], $options->fields());
    }

    public function testRequestAndStreamDebugOutputAndSerializationDoNotExposeCredentials(): void
    {
        $handle = fopen('php://temp', 'w+b'); fwrite($handle, 'fixture-secret'); rewind($handle);
        try {
            $file = LocalFile::fromStream($handle, 'fixture-secret.mp4');
            $request = new ImportUrlRequest('https://example.invalid/video.mp4?signature=fixture-secret');
            self::assertStringNotContainsString('fixture-secret', print_r($file, true));
            self::assertStringNotContainsString('fixture-secret', print_r($request, true));
            $this->expectException(\LogicException::class);
            serialize($file);
        } finally { fclose($handle); }
    }
}
