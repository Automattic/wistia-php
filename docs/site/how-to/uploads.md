# Upload files and import URLs

Use `UploadService` to send a local file, import a direct URL, copy an S3 object, update a thumbnail or import supplied localization audio.

You need a [configured client](../tutorials/first-api-call.md#create-the-client) and upload permission. Uploads/imports **change remote content and can consume storage or quota**. Run them only for files and accounts you intend to modify.

## Upload a local file

```php
use Automattic\Wistia\UploadService;
use Automattic\Wistia\Request\Upload\UploadFileRequest;
use Automattic\Wistia\Upload\LocalFile;
use Automattic\Wistia\Upload\UploadOptions;

$uploads = new UploadService($client);
$result = $uploads->uploadFile(new UploadFileRequest(
    LocalFile::fromPath('/absolute/path/video.mp4', contentType: 'video/mp4'),
    new UploadOptions(name: 'Product walkthrough'),
));
$mediaId = $result->data->hashed_id;
```

Replace the path with a readable, nonempty local file. The client streams the bytes and closes the handle it opened, even on failure. It does not validate the actual video codec or remote size/plan eligibility.

`UploadOptions` supports `project_id`, `name`, `description` and `contact_id`. Upload retains the name `project_id`, not Data's `folder_id`.

## Import a direct URL

This creates media too. Pass the original complete URL, without pre-encoding its query:

```php
use Automattic\Wistia\Request\Upload\ImportUrlRequest;

$imported = $uploads->importUrl(new ImportUrlRequest(
    'https://your-public-host.example/video.mp4',
    low_priority: false,
));
```

Use a direct HTTP(S) file URL with a file extension. It must be accessible to Wistia. Signed grants may need to remain valid for hours because fetching can happen later; do not log them. Embedded credentials, fragments and malformed URLs are rejected locally.

This Upload-host import returns an Upload media result. It is not `DataMediaService::importMediaFromURL()`, which returns a modern Data background-job result.

## Copy an S3 object

First arrange bucket access with [Wistia support](https://docs.wistia.com/reference/upload-via-s3-copy). Wistia needs to register your bucket and enable read permissions on both sides, including `GetObject` and `GetObjectVersion`. The client does not configure IAM or make AWS calls itself.

```php
use Automattic\Wistia\Request\Upload\CopyFromS3Request;

$copied = $uploads->copyFromS3(new CopyFromS3Request(
    bucket: 'your-bucket-name',
    source_file: 'videos/walkthrough.mp4',
    region: 'eu-west-1',
    options: new UploadOptions(name: 'Product walkthrough'),
));
$copiedMediaId = $copied->data->hashed_id;
```

Use a bucket name, not an ARN or URL, and an object key without a leading slash. Pass the original key without URL-encoding it. Omit `region` only when the bucket is in `us-east-1`, Wistia's default. Object existence, IAM permissions and region matching are checked remotely.

Normal success is HTTP 200 with the same Upload media result as a regular upload, including an optional `account_id` projection. The undocumented `create_media=false` mode is not exposed.

Non-2xx responses raise [typed exceptions](errors.md) even when the body is empty or non-JSON, or JSON is labelled `text/plain`. The client classifies errors by status and keeps remote bodies out of exceptions.

## Understand accepted results

`Upload\Media` differs from Data media. Fields such as `hashed_id`, `progress` and `status` can be absent; inspect them before use. A successful response does not prove the file is playable.

The service does not poll processing or invent a job ID. Use an appropriate media read in your application to reconcile progress. A timeout or stopped PHP process does not cancel remote processing.

## Other supported upload workflows

| Method | Inputs | Successful result |
| --- | --- | --- |
| `uploadThumbnailFile` | Media ID and image `LocalFile` | Status-only result, HTTP 201 |
| `importThumbnailUrl` | Media ID and direct image URL | Status-only result, HTTP 201 |
| `importLocalizationAudio` | Media ID, language tag and supplied audio URL; optional overwrite flag | Status-only result, HTTP 201 |

The localization method imports **existing audio**; it does not order translation, dubbing or generation. Use a language tag such as `es-419`. Omit overwrite unless you intend replacement. Thumbnail and supplied-audio acceptance do not guarantee processing/application completion.

See [UploadService](../../../src/UploadService.php) for the supported request/result classes.

## Bring your own stream carefully

`LocalFile::fromStream()` uploads from the current position of a readable, seekable regular-file or temporary stream. It leaves the stream open; you close it. Do not concurrently seek, truncate or mutate a source while uploading.

Every request is one attempt. If an upload's result is [uncertain](errors.md), reconcile before sending the file again.
