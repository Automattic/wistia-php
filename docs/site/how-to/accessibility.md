# Manage captions and accessibility

Read captions, import supplied text and edit existing tracks with explicit services. You can also read speaker profiles and manage existing localizations/audio descriptions; purchasing or generating those assets is not part of these helpers.

Use a [configured client](../tutorials/first-api-call.md#create-the-client), a media ID and appropriate media permissions. Transcript text, speaker identities and download URLs can be sensitive.

## Read a transcript

```php
use Automattic\Wistia\DataCaptionsService;
use Automattic\Wistia\Request\Data\CaptionFormat;
use Automattic\Wistia\Request\Data\ShowCaptionsRequest;

$captions = new DataCaptionsService($client);
$result = $captions->showCaptions(new ShowCaptionsRequest(
    'YOUR_MEDIA_HASHED_ID',
    'eng',
    include_speakers: true,
    format: CaptionFormat::Txt,
));
$text = $result->data;
```

TXT, SRT and VTT return text bytes, not a JSON record. The client does not transcode them or change line endings. `include_speakers` affects TXT only. JSON is the default format and can include segments or diarized segments.

A diarized read can start server-side speaker-data generation and return a processing state. It is not a speaker-assignment write or an account-setting change.

## Import or replace supplied text

Create/update caption requests take **SRT contents** in `caption_file`, not a local pathname or multipart file. Read a file in your application first. These operations write caption tracks; they do not purchase captions.

Deleting a track removes it remotely. Review the intended language and media before submitting any change.

## Make a version-guarded edit

The following writes to an existing track. Replace the placeholders, reread the current version and reassess the match before applying it:

```php
use Automattic\Wistia\Accessibility\CaptionEdit;
use Automattic\Wistia\Request\Data\EditCaptionsTextRequest;

$current = $captions->showCaptions(
    new ShowCaptionsRequest('YOUR_MEDIA_HASHED_ID', 'eng', include: 'segments'),
);
if ($current->data->version === null) {
    throw new RuntimeException('A caption version is required before editing.');
}
$edited = $captions->editCaptionsText(new EditCaptionsTextRequest(
    'YOUR_MEDIA_HASHED_ID',
    'eng',
    [new CaptionEdit('old wording', 'new wording', 0, 5000)],
    expected_version: $current->data->version,
));
```

An edit batch contains 1–20 edits and applies atomically. Time bounds are inclusive milliseconds and must be supplied together. Empty replacement text deletes a match. Fuzzy search suggestions are not automatically applied.

HTTP 409 can mean a stale version or diverged inactive versions. Sanitized exceptions expose the status, not which reason occurred. Do not assume repeated rereads resolve every conflict; keep recovery bounded and escalate persistent conflicts. HTTP 422 can indicate a match/boundary failure. A transport/decoding failure may leave the write [uncertain](errors.md).

## Find and list tracks

`findCaptionMatches()` returns per-media outcomes even on HTTP 200. Inspect statuses, exact matches and separate suggestions. It accepts at most 50 unique media IDs and 500 target-text characters; success does not imply every target matched or is editable.

Caption list methods have bounded pagination where supplied. Cursor sorting for captions supports only `id`. Metadata-only results may omit text. Use [DataCaptionsService](../../../src/DataCaptionsService.php) for request/result details.

## Work with other accessibility assets

| Task | Service | Boundary |
| --- | --- | --- |
| Read speaker profiles | `DataSpeakersService` | No speaker-assignment writes |
| List/show/delete existing localizations | `DataLocalizationsService` | No localization generation or purchase |
| List/show/delete existing audio descriptions; read order status | `DataExtendedAudioDescriptionsService` | No creation of orders or automatic polling |
| Import supplied localization audio | `UploadService` | [Existing audio only](uploads.md), not generated translation |

Order results may not contain a download link until available. If you poll an existing order yourself, use finite bounds. Stopping local reads does not cancel it. Deletion and feature access remain subject to Wistia's permissions and plan rules.
