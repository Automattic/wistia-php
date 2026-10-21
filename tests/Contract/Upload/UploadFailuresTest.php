<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Contract\Upload;

use Automattic\Wistia\{Client, Configuration, UploadService};
use Automattic\Wistia\Exception\{ApiException, AuthenticationException, DecodeException, PermissionException, RateLimitException, TransportException, WistiaException};
use Automattic\Wistia\Http\TransportResponse;
use Automattic\Wistia\Request\Upload\{UploadFileRequest, ImportUrlRequest, CopyFromS3Request, UploadThumbnailFileRequest, ImportThumbnailUrlRequest, ImportLocalizationAudioRequest};
use Automattic\Wistia\Upload\LocalFile;
use Automattic\Wistia\Tests\Offline\{ExceptionAssertions, RecordingTransport};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UploadFailuresTest extends TestCase
{
    public static function workflows(): iterable
    {
        foreach (['uploadFile', 'importUrl', 'copyFromS3', 'uploadThumbnailFile', 'importThumbnailUrl', 'importLocalizationAudio'] as $method) { yield $method => [$method]; }
    }
    private function invoke(#[\SensitiveParameter] UploadService $service, string $method): mixed
    {
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, 'fixture-secret-file-bytes'); rewind($stream);
        $file = LocalFile::fromStream($stream, 'fixture.mp4');
        try {
            $request = match ($method) {
                'uploadFile' => new UploadFileRequest($file),
                'importUrl' => new ImportUrlRequest('https://example.invalid/video.mp4?signature=fixture-secret'),
                'copyFromS3' => new CopyFromS3Request('example-bucket', 'fixture-secret.mp4'),
                'uploadThumbnailFile' => new UploadThumbnailFileRequest('media123', $file),
                'importThumbnailUrl' => new ImportThumbnailUrlRequest('media123', 'https://example.invalid/thumb.jpg?signature=fixture-secret'),
                'importLocalizationAudio' => new ImportLocalizationAudioRequest('media123', 'fr', 'https://example.invalid/audio.mp3?signature=fixture-secret'),
            };
            return $service->$method($request);
        } finally { self::assertIsResource($stream); fclose($stream); }
    }
    public static function errors(): iterable
    {
        foreach (self::workflows() as [$method]) {
            foreach ([401 => AuthenticationException::class, 403 => PermissionException::class, 429 => RateLimitException::class,
                400 => ApiException::class, 500 => ApiException::class, 408 => ApiException::class, 302 => ApiException::class] as $status => $exception) {
                yield "$method-$status" => [$method, $status, $exception];
            }
        }
    }
    #[DataProvider('errors')]
    public function testErrorsAreSanitizedSingleAttemptAndRetainRateHints(string $method, int $status, string $exception): void
    {
        $transport = new RecordingTransport(new TransportResponse($status,
            ['Content-Type' => ['text/html'], 'Retry-After' => ['3']], 'fixture-secret remote failure'));
        try { $this->invoke(new UploadService(new Client(new Configuration('fixture-secret'), $transport)), $method); self::fail('Must fail.'); }
        catch (WistiaException $e) {
            self::assertInstanceOf($exception, $e);
            self::assertSame($status, $e->status);
            self::assertSame($status === 408 || $status >= 500 || $status === 302, $e->remoteOutcomeUncertain);
            self::assertSame('3', $e->rateLimit->retryAfter);
            ExceptionAssertions::assertSanitized($e);
        }
        self::assertCount(1, $transport->requests);
    }
    #[DataProvider('workflows')]
    public function testTransportFailuresRemainUncertainAndAreNotRetried(string $method): void
    {
        $transport = new RecordingTransport(new TransportResponse(200, [], ''));
        $transport->failure = new \RuntimeException('fixture-secret');
        try { $this->invoke(new UploadService(new Client(new Configuration('fixture-secret'), $transport)), $method); self::fail(); }
        catch (TransportException $e) { self::assertTrue($e->remoteOutcomeUncertain); ExceptionAssertions::assertSanitized($e); }
        self::assertCount(1, $transport->requests);
    }
    public static function invalidResponses(): iterable
    {
        foreach (self::workflows() as [$method]) {
            $empty = !in_array($method, ['uploadFile', 'importUrl', 'copyFromS3'], true);
            yield "$method-wrong-status" => [$method, $empty ? 200 : 201, 'application/json', $empty ? '' : '{}'];
            yield "$method-wrong-body" => [$method, $empty ? 201 : 200, 'application/json', $empty ? '{}' : '{"progress":2}'];
            if (!$empty) {
                yield "$method-empty-body" => [$method, 200, 'application/json', ''];
                yield "$method-wrong-type" => [$method, 200, 'text/html', '{}'];
                yield "$method-malformed" => [$method, 200, 'application/json', '{'];
                yield "$method-wrong-shape" => [$method, 200, 'application/json', '[]'];
                yield "$method-null-field" => [$method, 200, 'application/json', '{"id":null}'];
                yield "$method-wrong-nested" => [$method, 200, 'application/json', '{"thumbnail":{"width":"160"}}'];
            }
        }
    }
    #[DataProvider('invalidResponses')]
    public function testWrongSuccessContractsAreUncertainDecodeFailures(string $method, int $status, string $contentType, string $body): void
    {
        $transport = new RecordingTransport(new TransportResponse($status, ['Content-Type' => [$contentType]], $body));
        try { $this->invoke(new UploadService(new Client(new Configuration('fixture-secret'), $transport)), $method); self::fail(); }
        catch (DecodeException $e) { self::assertTrue($e->remoteOutcomeUncertain); ExceptionAssertions::assertSanitized($e); }
        self::assertCount(1, $transport->requests);
    }
}
