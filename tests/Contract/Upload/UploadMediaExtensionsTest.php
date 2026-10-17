<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Contract\Upload;

use Automattic\Wistia\{Client, Configuration, UploadService};
use Automattic\Wistia\Http\TransportResponse;
use Automattic\Wistia\Request\Upload\{UploadFileRequest, ImportUrlRequest};
use Automattic\Wistia\Tests\Offline\RecordingTransport;
use Automattic\Wistia\Upload\LocalFile;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UploadMediaExtensionsTest extends TestCase
{
    public static function extensions(): iterable
    {
        foreach (['uploadFile', 'importUrl'] as $method) {
            yield "$method-absent" => [$method, '{}', [], [null, null, null, null]];
            yield "$method-observed-types" => [$method, '{"status":"queued","archived":false,"description":"","account_id":9}',
                ['status' => 'queued', 'archived' => false, 'description' => '', 'account_id' => 9], ['queued', false, '', 9]];
            yield "$method-null" => [$method, '{"status":null,"archived":null,"description":null,"account_id":null}',
                ['status' => null, 'archived' => null, 'description' => null, 'account_id' => null], [null, null, null, null]];
            yield "$method-other-open-schema-types" => [$method, '{"status":{"phase":"queued"},"archived":0,"description":["note"],"account_id":"unrepresentable","future_metadata":{"x":1}}',
                ['status' => (object) ['phase' => 'queued'], 'archived' => 0, 'description' => ['note'], 'account_id' => 'unrepresentable', 'future_metadata' => (object) ['x' => 1]], [null, null, null, null]];
        }
    }

    #[DataProvider('extensions')]
    public function testOpenSchemaMetadataNeverRejectsAnOtherwiseValidUpload(string $method, string $extraJson, array $extra, array $projections): void
    {
        $fixture = json_decode($extraJson);
        $fixture->id = 7;
        $fixture->hashed_id = 'fixture123';
        $transport = new RecordingTransport(new TransportResponse(200, ['Content-Type' => ['application/json']], json_encode($fixture, JSON_THROW_ON_ERROR)));
        $service = new UploadService(new Client(new Configuration('fixture-secret'), $transport));
        $handle = fopen('php://temp', 'w+b'); fwrite($handle, 'synthetic-file'); rewind($handle);
        try {
            $request = $method === 'uploadFile'
                ? new UploadFileRequest(LocalFile::fromStream($handle, 'test.mp4'))
                : new ImportUrlRequest('https://example.invalid/test.mp4');
            $result = $service->$method($request);
            self::assertSame(200, $result->status);
            self::assertSame(7, $result->data->id);
            self::assertSame('fixture123', $result->data->hashed_id);
            self::assertSame($projections, [$result->data->status, $result->data->archived, $result->data->description, $result->data->account_id]);
            self::assertEquals($extra, $result->data->additionalFields);
            self::assertEqualsCanonicalizing([...array_keys($extra), 'id', 'hashed_id'], $result->data->presentFields);
            self::assertCount(1, $transport->requests);
        } finally { fclose($handle); }
    }
}
