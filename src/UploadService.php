<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\{Body, Query};
use Automattic\Wistia\Http\{ApiFamily, EmptyDecoder, HttpMethod, Request, RequestSafety, ResponseDecoder};
use Automattic\Wistia\Request\Upload\{UploadFileRequest, ImportUrlRequest, CopyFromS3Request, UploadThumbnailFileRequest, ImportThumbnailUrlRequest, ImportLocalizationAudioRequest};
use Automattic\Wistia\Result\Upload\{UploadFileResult, ImportUrlResult, CopyFromS3Result, UploadThumbnailFileResult, ImportThumbnailUrlResult, ImportLocalizationAudioResult};
use Automattic\Wistia\Result\Result;
use Automattic\Wistia\Upload\LocalFile;
use Psr\Http\Message\StreamInterface;

final readonly class UploadService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function uploadFile(#[\SensitiveParameter] UploadFileRequest $request): UploadFileResult
    {
        return $this->sendFile($request->file, new Request(ApiFamily::Upload, HttpMethod::Post, '/',
            RequestSafety::MayChangeRemoteState, [200]), new Upload\Decoder(UploadFileResult::class),
            $request->options?->fields() ?? []);
    }

    public function importUrl(#[\SensitiveParameter] ImportUrlRequest $request): ImportUrlResult
    {
        $fields = ['url' => $request->url] + ($request->options?->fields() ?? []);
        if ($request->low_priority !== null) { $fields['low_priority'] = $request->low_priority; }
        return $this->client->execute(new Request(ApiFamily::Upload, HttpMethod::Post, '/',
            RequestSafety::MayChangeRemoteState, [200], body: self::form($fields)),
            new Upload\Decoder(ImportUrlResult::class))->result;
    }

    public function copyFromS3(#[\SensitiveParameter] CopyFromS3Request $request): CopyFromS3Result
    {
        $fields = ['bucket' => $request->bucket, 'source_file' => $request->source_file]
            + ($request->options?->fields() ?? []);
        if ($request->region !== null) { $fields['region'] = $request->region; }
        return $this->client->execute(new Request(ApiFamily::Upload, HttpMethod::Post, '/s3_copy',
            RequestSafety::MayChangeRemoteState, [200], body: self::form($fields)),
            new Upload\Decoder(CopyFromS3Result::class))->result;
    }

    public function uploadThumbnailFile(#[\SensitiveParameter] UploadThumbnailFileRequest $request): UploadThumbnailFileResult
    {
        $this->sendFile($request->file, new Request(ApiFamily::Upload, HttpMethod::Post, '/thumbnail',
            RequestSafety::MayChangeRemoteState, [201], new Query([['media_id', $request->media_id]])), new EmptyDecoder());
        return new UploadThumbnailFileResult(201);
    }

    public function importThumbnailUrl(#[\SensitiveParameter] ImportThumbnailUrlRequest $request): ImportThumbnailUrlResult
    {
        $response = $this->client->execute(new Request(ApiFamily::Upload, HttpMethod::Post, '/thumbnail',
            RequestSafety::MayChangeRemoteState, [201], new Query([['media_id', $request->media_id]]),
            self::form(['url' => $request->url])), new EmptyDecoder());
        return new ImportThumbnailUrlResult($response->status);
    }

    public function importLocalizationAudio(#[\SensitiveParameter] ImportLocalizationAudioRequest $request): ImportLocalizationAudioResult
    {
        $fields = ['url' => $request->url, 'localization_language' => $request->localization_language,
            'localization_media_id' => $request->localization_media_id];
        if ($request->overwrite_existing_localization !== null) {
            $fields['overwrite_existing_localization'] = $request->overwrite_existing_localization;
        }
        $response = $this->client->execute(new Request(ApiFamily::UploadLocalization, HttpMethod::Post, '/',
            RequestSafety::MayChangeRemoteState, [201], body: self::form($fields)), new EmptyDecoder());
        return new ImportLocalizationAudioResult($response->status);
    }

    private function sendFile(#[\SensitiveParameter] LocalFile $file, #[\SensitiveParameter] Request $request,
        #[\SensitiveParameter] ResponseDecoder $decoder, #[\SensitiveParameter] array $fields = []): Result
    {
        return $file->withStream(function (#[\SensitiveParameter] StreamInterface $stream) use ($file, $request, $fields, $decoder): Result {
            $parts = [['name' => 'file', 'contents' => $stream, 'filename' => $file->filename,
                'headers' => ['Content-Type' => $file->contentType]]];
            foreach ($fields as $key => $value) { $parts[] = ['name' => $key, 'contents' => (string) $value]; }
            return $this->client->execute(new Request($request->family, $request->method, $request->path,
                $request->safety, $request->successStatuses, $request->query, Body::multipart($parts)), $decoder)->result;
        });
    }

    private static function form(#[\SensitiveParameter] array $fields): Body
    {
        $pairs = [];
        foreach ($fields as $key => $value) { $pairs[] = [$key, $value]; }
        return Body::form(new Query($pairs));
    }
    public function __debugInfo(): array { return []; }
}
