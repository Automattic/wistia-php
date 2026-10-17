# Upload contract evidence (P16, partial)

This is the sanitized evidence record for [#24](https://github.com/Automattic/wistia-php/issues/24). It supplements the pinned first-party sources in [operations.json](operations.json); it does not claim Wistia has corrected its documentation.

## Maintainer decision and scope

During the 2026-10-17 investigation, the maintainer separately authorized small live uploads/imports using a test account and then approved proceeding with the non-S3 workflows while leaving S3 unconfirmed pending an email response from Wistia human support. This authorizes the observed thumbnail/localization implementation below, not final whole-ticket acceptance, release, purchases, generation, cleanup/deletion or recurring live checks. The original source-documentation gaps remain visible in the inventory. #24 remains incomplete.

## Observed requests and responses

Direct HTTPS probes used Bearer headers, finite timeouts, no redirects and no retries. These were standalone Python probes, **not live tests of the PHP implementation**. One synthetic one-second video and a synthetic image were used. The URL-thumbnail probe reused an image URL returned by Wistia. Supplied audio was a 40,293-byte MP3 rendition of the CC0 [Silence 10sec.ogg](https://commons.wikimedia.org/wiki/File:Silence_10sec.ogg) sample. No generation or purchase endpoint was called, no overwrite flag was enabled, and resources were not deleted.

| Workflow | UTC observation on 2026-10-17 | Request | Response |
| --- | --- | --- | --- |
| Local video | 08:10:02 | POST `https://upload.wistia.com/`, multipart `file`, `name` | **200**, `application/json;charset=utf-8`, media JSON object |
| Thumbnail file | 08:10:04 | POST `https://upload.wistia.com/thumbnail`, multipart `media_id`, `file` | **201**, `application/json;charset=utf-8`, **zero-byte body** |
| Thumbnail URL | 08:11:18 | POST `https://upload.wistia.com/thumbnail?media_id=…`, form lowercase `url` | **201**, `application/json;charset=utf-8`, **zero-byte body** |
| Supplied audio localization | 08:13:14 | POST `https://upload-v2.wistia.com/`, form `url`, `localization_media_id`, `localization_language=es-419` | **201**, `application/json;charset=utf-8`, **zero-byte body** |

The local-video JSON had these field types: `id:int`, `hashed_id:string`, `progress:float`, `type:string`, `archived:bool`, `name:string`, `duration:float`, `created:string`, `updated:string`, `description:string`, `status:string`, `thumbnail:{url:string,width:int,height:int}`, `account_id:int`. Private identifiers, account metadata, credentials and delivery URLs are intentionally omitted. Raw responses remain private local investigation artifacts, not public fixtures.

A declared JSON content type does **not** make the three empty responses JSON documents. Their decoders require an empty body and return operation-specific acceptance results, not guessed Media objects. The thumbnail-file PHP method uses the guide's documented `media_id` query form; the live file probe used its also-documented multipart form.

## First-party support and unresolved claims

- [Root Upload reference](https://docs.wistia.com/reference/post_.md) documents the regular upload/import **200 JSON** media contract. The URL-import variant has offline contract coverage only; it was not separately called live.
- [Thumbnail guide](https://docs.wistia.com/reference/update-thumbnails-via-upload-api.md), updated 2025-04-16, documents POST and lowercase `url`, but its URL example omits POST and uses uppercase `URL`. The observed POST/lowercase form works. No GET/uppercase variant was tried and no claim is made that it cannot work.
- [Upload guide](https://docs.wistia.com/reference/getting-started-with-the-upload-api-1.md), updated 2025-11-11, explicitly illustrates `upload-v2.wistia.com` for supplied localization audio, conflicting with its general upload-host prose. The example host accepted the supplied-audio request. The other host was not tried; no fallback or host equivalence is claimed.
- [S3-copy guide](https://docs.wistia.com/reference/upload-via-s3-copy.md), updated 2026-01-14, documents setup, authentication and input fields but omits success status, content type and body. The current and `v2026.03` pages were checked. The root Upload schema and historical `wistia/wistia-open-docs` upload guide do not explicitly extend their response contracts to `/s3_copy`. AWS CopyObject documentation concerns the AWS response, not the Wistia-facing response.
- The maintainer relayed a Wistia automated-agent answer suggesting **201 JSON media** for S3. That is **unconfirmed guidance**, not a fixture oracle or human confirmation. A human-support email is pending. No S3/AWS setup or request was made; `copyFromS3` remains unimplemented.

These probes establish request acceptance on one account, not completed video processing, applied thumbnail state, playable localization, all plan entitlements, every failure response, or an enduring upstream guarantee. There are no returned background-job IDs for the empty responses; none are invented. The PHP service performs no automatic polling or resubmission. Root upload results expose media IDs and processing progress/status when supplied; callers can use existing Data reads to inspect the media separately.

## Offline coverage

[Upload contract tests](../../tests/Contract/Upload/) use synthetic metadata and responses derived from the documented root schema and the observed empty-response contracts. They cover each implemented request/decoder, Bearer-only authentication, unversioned family hosts, signed-URL preservation, status/content/body errors, permission/rate/transport failures and uncertain outcomes without replay. [Streaming checks](../../tests/Offline/UploadStreamingTest.php) exercise the actual Guzzle cURL factory with synthetic transfer completion and no network, checking multipart bytes, known lengths, nonrewindability, one dispatch and file ownership. [Validation checks](../../tests/Offline/UploadValidationTest.php) cover local failures before sending.

Executed runtime results and independent-review status are reported with the delivery; authored coverage alone is not a passed check. Ordinary offline discovery does not load `.env` or run these live probes. No reusable live harness or default live execution is added.
