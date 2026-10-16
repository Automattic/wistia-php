# Captions and accessibility

P08 / [#16](https://github.com/Automattic/wistia-php/issues/16) adds 16 operations for captions, speaker-profile reads, existing localizations, and existing extended audio descriptions/orders. Construct the four services directly around `Client`; their [Data reference](reference/data.md#captions-and-accessibility) maps every request, result, restriction and offline contract test. All use Bearer authentication at `/modern` with version `2026-09`.

## Read captions

```php
use Automattic\Wistia\DataCaptionsService;
use Automattic\Wistia\Request\Data\{ListCaptionsRequest, ShowCaptionsRequest, CaptionFormat, Cursor};

$captions = new DataCaptionsService($client);
$page = $captions->listCaptions(new ListCaptionsRequest(
    media_ids: ['media-one', 'media-two'], languages: ['eng'], include: 'metadata',
));
foreach ($captions->paginateCaptions(new ListCaptionsRequest(
    languages: ['eng'], per_page: 100, cursor: new Cursor(enabled: 1), sort_by: 'id',
), maxPages: 20)->items() as $track) {
    // Metadata-only results can omit text. Inspect presentFields to distinguish omission from null.
}
$transcript = $captions->showCaptions(new ShowCaptionsRequest(
    'media-one', 'zh-Hant', include_speakers: true, format: CaptionFormat::Txt,
));
$text = $transcript->data; // Exact text bytes, not JSON; no transcoding or line-ending changes.
```

JSON is the default format. `CaptionFormat::Srt`, `Vtt`, and `Txt` select path extensions; callers need not manage Accept headers. SRT/TXT require `text/plain`; VTT requires `text/vtt`. `include_speakers` affects TXT only. JSON `include: 'segments'` exposes timed cues; `include: 'diarized_segments'` exposes typed speaker turns, detected IDs, labels and availability. Use the detected ID, not the display-only `Speaker 2` label, as an identifier. A diarized read can start server-side speaker-data generation and report `processing`; it is not a speaker-assignment write. `disabled` reflects account settings, while `unavailable` and `diarization_derivable` describe whether generation is possible. Reading never changes account settings.

List captions supports individual pages and bounded lazy offset/cursor traversal. Cursor ordering supports only `id`; keep sort order unchanged between pages. Repeated `media_ids[]` and `languages[]` keys are retained, not converted to numeric array indexes. `DataSpeakersService::paginateSpeakers` and `DataExtendedAudioDescriptionsService::paginateMediaExtendedAudioDescriptions` preserve their own filters and sort choices. `listCaptionsByMedia` and localization lists have no documented pagination. A short/empty final page ends traversal; cursor cycles and page-bound exhaustion fail explicitly.

## Import, replace, or correct caption text

Create/replace requests accept SRT **contents** in the JSON `caption_file` field; read local files in your application first. These operations do not buy captions. `CreateCaptionsRequest` optionally carries `language`; `UpdateCaptionsRequest` replaces the selected language track. File streaming/multipart upload is not exposed by these methods. `deleteCaptions` removes the selected track; deletion is a remote destructive action and is never exercised by the default tests.

```php
use Automattic\Wistia\Accessibility\CaptionEdit;
use Automattic\Wistia\Request\Data\{FindCaptionMatchesRequest, EditCaptionsTextRequest};

$found = $captions->findCaptionMatches(new FindCaptionMatchesRequest(
    ['media-one'], 'old wording', language_code: 'zh-Hant', start_ms: 0, end_ms: 5000,
));
$current = $captions->showCaptions(new ShowCaptionsRequest('media-one', 'zh-Hant', include: 'segments'));
if ($current->data->version !== null) {
    $edited = $captions->editCaptionsText(new EditCaptionsTextRequest(
        'media-one', 'zh-Hant', [new CaptionEdit('old wording', 'new wording', 0, 5000)],
        expected_version: $current->data->version,
    ));
}
```

Search returns per-media outcomes even on HTTP 200. Inspect `results[*].status`, `match_status`, exact `matches` and separate fuzzy `suggestions`; `complete` does not imply every media succeeded or every target matched. At most 50 unique media IDs and 500 target-text characters are accepted. `occurrence` is one-based and can retrieve matches after the first ten; retain `matches_truncated` and `next_occurrence`. Missing/inaccessible media use nondisclosing per-media statuses. Finding text does not establish edit permission or guarantee a later write will succeed.

Edits contain 1–20 typed `CaptionEdit` values and a required `expected_version`; the entire batch applies atomically or not at all. Empty replacement text deletes a match. Time bounds are inclusive milliseconds in the requested media's coordinates and must be supplied together. Case, punctuation and whitespace normalization, ambiguous matches, unsupported edit boundaries and composite-media authorization remain server decisions; fuzzy suggestions are never silently applied. Composite results can name a source `media_id` different from the requested media. HTTP 409 has two documented version-guard cases: `stale_version` calls for rereading the active caption and reassessing the edit; `diverged` means newer inactive versions exist, so rereading will not help. For divergence, Wistia recommends waiting and retrying, then escalating if it persists. The shared sanitized `ApiException` exposes status 409 but not which reason occurred; applications cannot distinguish these cases from the exception. Keep recovery attempts bounded and escalate persistent conflicts rather than looping on rereads or blindly resubmitting edits. HTTP 422 can report an edit/boundary failure. Raw transcript-bearing error details remain hidden. There is no automatic write retry; callers own reconciliation, including ambiguous failures.

## Existing localizations and audio descriptions

`DataLocalizationsService` lists, shows and deletes existing localizations. `include_transcript` retains its explicit boolean; results include typed source media and an optional/nullable transcript. Deletion can be refused remotely, including constraints on the media's primary audio track. This service does not generate or buy localizations; supplied-audio imports remain P16 / #24.

`DataExtendedAudioDescriptionsService` lists, shows and deletes existing descriptions and reads existing orders through `getOrderStatus`. Description assets retain nullable MP3 links and WebVTT text. Order states are `in_progress`, `completed`, `cancelled`, and `failed`; a result link can be absent/null until available. Order-status reads are single requests, not purchases or an automatic poller. Applications choosing to poll must set their own finite attempt/time bound and stop on a terminal state; stopping local reads does not cancel a remote order.

Speaker profiles are read-only here. Media speaker-assignment writes are not part of this ticket. Caption purchases, text translation, localization generation, audio-description orders, bulk purchases and account trials remain excluded; no arbitrary-operation escape hatch is provided.

## Permissions, failures, and evidence

Per-operation token scopes, delegated-contact authorization and expiring-token permissions are retained in the [reference](reference/data.md#captions-and-accessibility). Transcript-edit authorization on a composite may also require access to its source media. Unsupported features/entitlements fail remotely, commonly with 403. An unspecified plan requirement is not proof of free-plan access.

Typed nested results preserve omitted fields separately from explicit null via `presentFields`. Requests/results hide content in diagnostic dumps; applications must still protect transcript text, speaker identities, download URLs, tokens and fixtures in their own storage/logs. Authentication, rate limits, transport and malformed-response errors use the shared sanitized exceptions. The shared client conservatively marks ambiguous POST outcomes uncertain even for caption matching, which itself is read-only. No request is replayed automatically.

Contract tests use synthetic fixtures and controlled transports. They cover wire paths, exact filters/body names, version/authentication, text bytes, nested results, pagination, local validation, conflict/no-retry behavior, mixed match outcomes and order states. They do not establish live Wistia behavior, paid-plan entitlement or account access. No credentials, purchases or live destructive calls are used.

For migration, replace legacy caption magic calls with these explicit services and named request/result objects. Use `CaptionFormat` instead of a global format switch; handle exceptions instead of legacy raw error-code objects. Other API families remain planned unless marked delivered in the inventory.
