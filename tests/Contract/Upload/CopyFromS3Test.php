<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Contract\Upload;

use Automattic\Wistia\{Client, Configuration, UploadService};
use Automattic\Wistia\Exception\{ApiException, AuthenticationException, PermissionException, WistiaException};
use Automattic\Wistia\Http\TransportResponse;
use Automattic\Wistia\Request\Upload\CopyFromS3Request;
use Automattic\Wistia\Result\Upload\CopyFromS3Result;
use Automattic\Wistia\Upload\UploadOptions;
use Automattic\Wistia\Tests\Offline\{ExceptionAssertions, RecordingTransport};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CopyFromS3Test extends TestCase
{
    public function testCopyUsesConfirmedUploadMediaContractAndExactFormFields(): void
    {
        // Synthetic response based on the maintainer-relayed human-support confirmation, not a live S3 probe.
        $transport = new RecordingTransport(new TransportResponse(200, ['Content-Type' => ['application/json;charset=utf-8']],
            '{"id":9,"hashed_id":"s3media123","name":"Example","type":"Video","created":"2026-10-17T00:00:00Z","updated":"2026-10-17T00:00:00Z","duration":1.5,"progress":0.5,"thumbnail":{"url":"https://example.invalid/thumb.jpg","width":160,"height":90},"account_id":42,"future_field":"retained"}'));
        $service = new UploadService(new Client(new Configuration('fixture-secret'), $transport));
        $key = 'videos/a + b%2Fc&part=1/été.mp4';
        $input = new CopyFromS3Request('example-bucket', $key, 'eu-west-1',
            new UploadOptions(project_id: 'folder123', name: 'A & B', description: '', contact_id: 7));
        $result = $service->copyFromS3($input);
        self::assertInstanceOf(CopyFromS3Result::class, $result);
        self::assertSame(200, $result->status);
        self::assertSame(9, $result->data->id);
        self::assertSame('s3media123', $result->data->hashed_id);
        self::assertSame(42, $result->data->account_id);
        self::assertSame(42, $result->data->additionalFields['account_id']);
        self::assertSame('retained', $result->data->additionalFields['future_field']);
        self::assertSame(0.5, $result->data->progress);
        self::assertSame(160, $result->data->thumbnail->width);
        self::assertCount(1, $transport->requests);
        $request = $transport->requests[0];
        self::assertSame('https://upload.wistia.com/s3_copy', $request->url);
        self::assertSame('POST', $request->method);
        self::assertSame(['Authorization' => 'Bearer fixture-secret', 'Content-Type' => 'application/x-www-form-urlencoded'], $request->headers);
        parse_str($request->body, $fields);
        self::assertSame(['bucket' => 'example-bucket', 'source_file' => $key, 'project_id' => 'folder123',
            'name' => 'A & B', 'description' => '', 'contact_id' => '7', 'region' => 'eu-west-1'], $fields);
        self::assertStringNotContainsString($key, print_r($input, true));
        self::assertStringNotContainsString('s3media123', print_r($result, true));
    }

    public function testOmittedOptionsKeepTheServerRegionDefaultAndDoNotSendInternalMode(): void
    {
        $transport = new RecordingTransport(new TransportResponse(200, ['Content-Type' => ['application/json']], '{}'));
        $service = new UploadService(new Client(new Configuration('fixture-secret'), $transport));
        $service->copyFromS3(new CopyFromS3Request('example-bucket', 'object-without-extension'));
        parse_str($transport->requests[0]->body, $fields);
        self::assertSame(['bucket' => 'example-bucket', 'source_file' => 'object-without-extension'], $fields);
    }

    public static function failureBodies(): iterable
    {
        foreach ([400 => ApiException::class, 401 => AuthenticationException::class,
            403 => PermissionException::class, 500 => ApiException::class] as $status => $exception) {
            foreach (['application/json', 'text/plain; charset=utf-8', 'text/html', ''] as $type) {
                foreach (['{"code":"upload_failed","detail":"fixture-secret"}', '', 'fixture-secret non-JSON'] as $body) {
                    yield [$status, $exception, $type, $body];
                }
            }
        }
    }

    #[DataProvider('failureBodies')]
    public function testFailuresDoNotDependOnContentTypeOrBody(int $status, string $exception, string $type, #[\SensitiveParameter] string $body): void
    {
        $transport = new RecordingTransport(new TransportResponse($status, $type === '' ? [] : ['Content-Type' => [$type]], $body));
        try {
            (new UploadService(new Client(new Configuration('fixture-secret'), $transport)))
                ->copyFromS3(new CopyFromS3Request('example-bucket', 'fixture-secret.mp4'));
            self::fail('Every non-2xx must fail.');
        } catch (WistiaException $e) {
            self::assertInstanceOf($exception, $e);
            self::assertSame($status, $e->status);
            self::assertSame($status >= 500, $e->remoteOutcomeUncertain);
            ExceptionAssertions::assertSanitized($e);
        }
        self::assertCount(1, $transport->requests);
    }

    public static function invalidInputs(): iterable
    {
        foreach (['', 'arn:aws:s3:::fixture-secret', 's3://fixture-secret', 'bucket/path', 'bucket with spaces'] as $bucket) {
            yield [$bucket, 'fixture-secret.mp4', null];
        }
        foreach (['', '/fixture-secret.mp4', "fixture-secret\n.mp4"] as $key) { yield ['example-bucket', $key, null]; }
        foreach (['', ' eu-west-1', 'fixture-secret/region'] as $region) { yield ['example-bucket', 'file.mp4', $region]; }
    }

    #[DataProvider('invalidInputs')]
    public function testInvalidInputIsSanitizedAndRejectedBeforeDispatch(#[\SensitiveParameter] string $bucket,
        #[\SensitiveParameter] string $key, #[\SensitiveParameter] ?string $region): void
    {
        $transport = new RecordingTransport(new TransportResponse(200, [], '{}'));
        try {
            (new UploadService(new Client(new Configuration('fixture-secret'), $transport)))
                ->copyFromS3(new CopyFromS3Request($bucket, $key, $region));
            self::fail('Invalid S3 input must fail locally.');
        } catch (\InvalidArgumentException $e) {
            self::assertStringNotContainsString('fixture-secret', (string) $e);
            self::assertNull($e->getPrevious());
        }
        self::assertCount(0, $transport->requests);
    }
}
