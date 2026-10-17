# Upload and import reference

Five workflows from [#24](https://github.com/Automattic/wistia-php/issues/24) are implemented. **S3 copy remains unimplemented pending human confirmation of its response contract.** See the [evidence record](../rewrite/upload-evidence.md) for the maintainer-approved thumbnail/localization observations and their limits. This is partial P16 delivery, not completion of the rewrite.

## Usage

Construct `UploadService` around a configured `Client`. Requests are under `Automattic\Wistia\Request\Upload`; results under `Automattic\Wistia\Result\Upload`. All requests use Bearer headers, no modern API-version header and no automatic retry, redirect or host fallback. The library does not read `.env`; applications own secret loading.

```php
use Automattic\Wistia\UploadService;
use Automattic\Wistia\Request\Upload\{UploadFileRequest, ImportUrlRequest};
use Automattic\Wistia\Upload\{LocalFile, UploadOptions};

// $client is configured as described in the root README.
$uploads = new UploadService($client);
$result = $uploads->uploadFile(new UploadFileRequest(
    LocalFile::fromPath('/path/to/video.mp4', contentType: 'video/mp4'),
    new UploadOptions(project_id: 'yourfolderid', name: 'My video'),
));
$mediaId = $result->data->hashed_id;

// Pass the original URL, including its signed query, without pre-encoding.
$import = $uploads->importUrl(new ImportUrlRequest(
    'https://example.com/video.mp4?signature=application-owned',
    low_priority: false,
));
```

### Files and ownership

`LocalFile::fromPath(path, ?filename, contentType)` accepts readable local regular files, not remote URLs, stream-wrapper paths or directories. The library opens the handle for each operation and closes it in a `finally` block on success or failure. Files are rechecked before sending; zero bytes, disappearance and unreadability produce a sanitized local `InvalidArgumentException`.

`LocalFile::fromStream(resource, filename, contentType)` accepts an open readable, seekable regular-file or temporary stream with bytes remaining. It uploads from the **current position**, without rewinding, and leaves the resource open on success or failure. The caller closes it. Closed, exhausted, write-only and nonseekable streams are rejected before sending. Read errors after sending become uncertain transport failures. Do not concurrently seek, truncate or mutate a source being uploaded.

File bytes are streamed through multipart and the default nonrewindable cURL handler, not read into one large string. The source has a known remaining length. Filename overrides must be plain names without path separators, quotes or control characters; content types are bare `type/subtype` values. Content defaults to `application/octet-stream`; supply an appropriate image type for thumbnail files. File content, actual codec/image validity and remote size limits remain server validation, not local content inspection.

### Validation and options

`UploadOptions` accepts optional `project_id`, `name`, `description`, `contact_id`. Omitted/null options are absent; empty description/name strings remain explicit. Names are at most 255 UTF-8 code points, contacts are positive integers and project/media identifiers are nonempty alphanumeric hashed IDs. Upload retains `project_id`, not modern Data's `folder_id`.

Source URLs must be direct HTTP(S) file URLs whose paths end in a file extension; thumbnail URLs require an image extension (`jpg`, `jpeg`, `png`, `gif`, `webp`, `bmp`, `tif`, `tiff`). User information, whitespace/control bytes and fragments are rejected. These are conservative library syntax guards, not exhaustive remote-format validation. Signed URLs are encoded once as complete values, preserving query order, repeated keys, plus signs and percent escapes after form decoding. Keep grants valid for hours: Wistia may fetch later. Do not log these URLs.

## upload-file

`uploadFile(UploadFileRequest): UploadFileResult` sends multipart POST to `https://upload.wistia.com/`. The request accepts `LocalFile $file` and optional `UploadOptions $options`. Success is **200 JSON** with typed `Upload\Media` in `data` and HTTP `status` on the result. This is not a modern Data Media result.

## import-url

`importUrl(ImportUrlRequest): ImportUrlResult` sends form POST to `https://upload.wistia.com/`. The request accepts `url`, optional `options` and optional boolean `low_priority`. False is sent as `false`, not omitted. Success is the same documented **200 JSON Upload media** shape, not the modern Data import endpoint's background-job result. This variant was not separately live-tested.

### Upload media results

`Upload\Media` exposes optional typed `id`, `hashed_id`, `name`, `type`, `created`, `updated`, `duration`, `progress` and nested `thumbnail` (`url`, `width`, `height`). The documented schema declares no required fields; missing fields map to null, with `presentFields` recording supplied properties. Explicit null for nonnullable wire fields, wrong types, nonfinite numbers and progress outside `[0,1]` fail decoding. Date-time fields retain source strings. Extra root fields are preserved in `additionalFields` because the Upload schema is open. The live-observed optional `status`, `archived`, `description` and `account_id` fields also have non-rejecting typed projections: matching string/bool/string/int values populate those properties; other values, including null, leave the projection null. All extension fields remain unchanged in `additionalFields`, including these four. A one-account observation does not narrow the documented open schema. Debug views hide metadata; explicitly reading it remains application-owned sensitive-data handling.

The returned media may still be processing. IDs/progress/status are available when supplied, but no background-job ID is invented and the service performs no polling. Use existing media reads separately as appropriate. A response does not assert playability, and stopping local work does not cancel processing.

## thumbnail-file

`uploadThumbnailFile(UploadThumbnailFileRequest): UploadThumbnailFileResult` accepts `media_id` and `LocalFile $file`. It sends POST `https://upload.wistia.com/thumbnail?media_id=…`, with multipart `file`. Supply an image. The maintainer-approved observed contract is **201 with an empty body**, even though Wistia declared a JSON content type. The operation-specific result contains `status`, not a fabricated media object.

## thumbnail-url

`importThumbnailUrl(ImportThumbnailUrlRequest): ImportThumbnailUrlResult` accepts `media_id` and `url`. It sends POST `https://upload.wistia.com/thumbnail?media_id=…`, with form lowercase `url`, following the guide's prose and the successful probe rather than its conflicting GET/uppercase example. The observed success is **201 with an empty body**; the result contains `status`. Applied thumbnail state was not separately checked.

## localization-import-url

`importLocalizationAudio(ImportLocalizationAudioRequest): ImportLocalizationAudioResult` accepts `localization_media_id`, `localization_language`, `url`, and optional boolean `overwrite_existing_localization`. This sends form POST to **`https://upload-v2.wistia.com/`**, the guide's localization-example host, confirmed to accept the test request. The general upload hostname was not tried for this workflow. There is no host fallback.

Use an existing supplied audio file and a hyphenated IETF language tag such as `es-419`. Local validation checks [RFC 5646](https://www.rfc-editor.org/rfc/rfc5646.html#section-2.1) tag structure and duplicate variants/extension singletons, including private-use and grandfathered forms, not registry membership or account availability. Incomplete `en-a`/`en-x` forms are rejected before sending; accepted input spelling is preserved. The media must belong to the token's account. Omit overwrite unless explicitly replacing a track; an explicit false stays false. This method never orders translation, AI dubbing or generated localization. The observed success is **201 with an empty body**; the result contains `status`, not a Data generation result or a completion guarantee. Playback and processing completion were not separately checked.

## s3-copy

`copyFromS3` is **not implemented**. Wistia's [S3 guide](https://docs.wistia.com/reference/upload-via-s3-copy.md) requires Wistia-assisted bucket setup and read permissions on both sides; it documents the request but not the response. A relayed automated-agent answer suggests 201 JSON media, but human confirmation is pending. No S3/AWS operation or success fixture is fabricated. S3 is still required #24 scope, not excluded from the final library.

## Failures, restrictions and migration

Upload permission is required. Remote ownership, plan/feature entitlement, URL availability, expired signatures and processing failures are not locally knowable; a successful test account call is not a guarantee for every plan. Wistia's generic guide formally prioritizes videos in folders/projects over audio/channel use; the specifically documented supplied-audio import is retained without claiming broader audio API support.

The shared exception contract preserves authentication (401), permission (403), rate-limit (429), other API, transport and decode distinctions. Rate hints remain accessible. Transport interruption, undeclared success status/body and potentially ambiguous 3xx/5xx/408 writes expose `remoteOutcomeUncertain`; reconcile before any manual retry. There is no resubmission helper. Error messages, causes and ordinary debug output do not reveal credentials or signed URLs; applications and injected transports must also avoid logging secrets.

Replace legacy magic upload dispatch and raw-object handling with the named request/result pairs above. Do not send `api_password` or token query/body fields, append `.json`, rename Upload parameters globally, assume every success is 200/JSON, or reuse modern Media/job result types. Default tests remain offline, load no credentials and do not repeat the separately approved live investigation.
