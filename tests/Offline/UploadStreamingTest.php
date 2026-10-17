<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\{Client, Configuration, UploadService};
use Automattic\Wistia\Exception\TransportException;
use Automattic\Wistia\Http\{GuzzleTransport, SingleAttemptCurlHandler};
use Automattic\Wistia\Request\Upload\UploadFileRequest;
use Automattic\Wistia\Upload\{LocalFile, UploadOptions};
use GuzzleHttp\{Client as GuzzleClient, HandlerStack};
use GuzzleHttp\Handler\EasyHandle;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UploadStreamingTest extends TestCase
{
    public static function transfers(): iterable
    {
        foreach ([false, true] as $borrowed) {
            foreach ([false, true] as $failure) { yield ($borrowed ? 'borrowed' : 'owned') . ($failure ? '-failure' : '-success') => [$borrowed, $failure]; }
        }
    }
    #[DataProvider('transfers')]
    public function testMultipartUsesRealSingleAttemptHandlerAndClosesOnlyOwnedFiles(bool $borrowed, bool $failure): void
    {
        $path = tempnam(sys_get_temp_dir(), 'wistia-upload-');
        $bytes = str_repeat('fixture-bytes-', 100000);
        file_put_contents($path, 'skip-' . $bytes);
        $handle = $borrowed ? fopen($path, 'rb') : null;
        if ($handle !== null) { fseek($handle, 5); }
        $file = $borrowed ? LocalFile::fromStream($handle, 'test.mp4', 'video/mp4') : LocalFile::fromPath($path, 'test.mp4', 'video/mp4');
        $observed = []; $calls = 0; $ownedHandles = [];
        $handler = new SingleAttemptCurlHandler(null, static function (#[\SensitiveParameter] EasyHandle $easy) use (&$observed, &$calls, &$ownedHandles, $path, $failure): void {
            ++$calls;
            foreach (get_resources('stream') as $resource) {
                if ((stream_get_meta_data($resource)['uri'] ?? null) === $path) { $ownedHandles[] = $resource; }
            }
            $body = $easy->request->getBody();
            $observed['seekable'] = $body->isSeekable();
            $observed['length'] = $easy->request->getHeaderLine('Content-Length');
            $observed['type'] = $easy->request->getHeaderLine('Content-Type');
            $observed['body'] = $body->getContents();
            $observed['rewindFailed'] = false;
            try { $body->rewind(); } catch (\RuntimeException) { $observed['rewindFailed'] = true; }
            if ($failure) { $easy->errno = 65; }
            else { $easy->response = new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], '{"id":1,"progress":0}'); }
        });
        try {
            $service = new UploadService(new Client(new Configuration('fixture-secret'), new GuzzleTransport(new GuzzleClient(['handler' => HandlerStack::create($handler)]))));
            try {
                $result = $service->uploadFile(new UploadFileRequest($file, new UploadOptions(name: 'A & B', description: '', contact_id: 7)));
                self::assertFalse($failure);
                self::assertSame(1, $result->data->id);
            } catch (TransportException $e) {
                self::assertTrue($failure); self::assertTrue($e->remoteOutcomeUncertain); ExceptionAssertions::assertSanitized($e);
            }
            self::assertSame(1, $calls);
            self::assertFalse($observed['seekable']);
            self::assertTrue($observed['rewindFailed']);
            self::assertSame((string) strlen($observed['body']), $observed['length']);
            self::assertStringContainsString($bytes, $observed['body']);
            self::assertStringContainsString('name="name"', $observed['body']);
            self::assertStringContainsString('A & B', $observed['body']);
            self::assertStringContainsString('name="description"', $observed['body']);
            self::assertStringContainsString('name="contact_id"', $observed['body']);
            self::assertStringNotContainsString('fixture-secret', $observed['body']);
            self::assertStringContainsString('multipart/form-data; boundary=', $observed['type']);
            self::assertNotEmpty($ownedHandles);
            foreach ($ownedHandles as $resource) { self::assertSame($borrowed, is_resource($resource)); }
            if ($borrowed) {
                self::assertSame(5 + strlen($bytes), ftell($handle));
                self::assertStringNotContainsString('skip-', $observed['body']);
            }
        } finally {
            if (is_resource($handle)) { fclose($handle); }
            unlink($path);
        }
    }

    public function testMultipartConstructionDoesNotReadWholeFileAndBodyDoesNotOwnBorrowedResource(): void
    {
        $handle = fopen('php://temp', 'w+b');
        fwrite($handle, str_repeat('x', 100000)); rewind($handle);
        try {
            $file = LocalFile::fromStream($handle, 'fixture.mp4');
            $file->withStream(static function (#[\SensitiveParameter] \Psr\Http\Message\StreamInterface $stream) use ($handle): void {
                $body = \Automattic\Wistia\Encoding\Body::multipart([['name' => 'file', 'contents' => $stream, 'filename' => 'fixture.mp4']]);
                self::assertSame(0, ftell($handle));
                self::assertGreaterThan(100000, $body->contents()->getSize());
                self::assertSame(0, ftell($handle));
                $body->contents()->close();
                self::assertIsResource($handle);
            });
        } finally { fclose($handle); }
    }
}
