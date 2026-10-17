# Non-purchasing bulk actions

`DataBulkActionsService::createBulkActions(CreateBulkActionsRequest)` submits `POST /modern/bulk` and returns `CreateBulkActionsResult`, containing `message` and a typed `Jobs\BackgroundJobStatus` in `backgroundJobStatus`. Only 202 JSON is accepted. Requests use Bearer authentication and pinned `X-Wistia-API-Version: 2026-09` through the shared `Client`.

The [first-party bulk contract](https://docs.wistia.com/reference/post_bulk.md) and [inventory](rewrite/operations.json) define the supported operation/resource combinations. This endpoint is not marked experimental. Coverage is synthetic and offline only; no live account, permission or feature entitlement has been verified.

## Explicit actions

Construct services around your configured `Client`. The following submits writes when executed with a real transport; it is not a credential-free smoke check:

```php
use Automattic\Wistia\Bulk\{Action, Change};
use Automattic\Wistia\DataBulkActionsService;
use Automattic\Wistia\Request\Data\CreateBulkActionsRequest;

$service = new DataBulkActionsService($client);
$request = new CreateBulkActionsRequest(actions: [
    new Action(Change::createSubfolder(folder_id: 'folder-hash', name: 'Edited clips')),
    new Action(Change::updateMedia(name: 'New title'), id: 'media-hash'),
    new Action(Change::moveMedia(folder_id: 'destination-folder'), id: 'another-media'),
]);
$result = $service->createBulkActions($request);
$jobId = $result->backgroundJobStatus->hashedId;
```

`Bulk\Change` has named typed factories, not a raw operation/payload constructor or arbitrary endpoint tunnel:

| Resource | Factories |
| --- | --- |
| `media` | `updateMedia`, `moveMedia`; no create (use uploads/imports). |
| `folder` | `createFolder`, `updateFolder`. |
| `subfolder` | `createSubfolder`, `updateSubfolder`. |
| `channel` | `createChannel`, `updateChannel`. |
| `channel_episode` | `createChannelEpisode`, `updateChannelEpisode`. |
| `captions` | `createCaptions`, `updateCaptions`. |
| `customization_*` | Update only: `updateAccess`, `updateAccessibility`, `updateAppearance`, `updateChapters`, `updateEngagement`, `updateLeadCapture`, `updatePlayback`, `updateRelatedMedia`, `updateSharing`, `updateThumbnail`. |

`Change::delete(ResourceType::Media)` and the corresponding `Folder`, `Subfolder`, `Channel`, `ChannelEpisode` and `Captions` enum cases create deletion changes. Customization deletion is rejected. **Deleting a folder or subfolder also soft-deletes its media**, unlike the single Delete Subfolder endpoint that preserves media at the folder root. Account owners/managers can restore media from trash only until it purges. Obtain the appropriate application/operator approval before executing destructive calls.

An `Action` takes a `Change` and requires a nonempty target `id` for update/delete/move; create has no target ID. Caption update/delete IDs are **caption-track IDs**, as returned by List Captions, not media IDs. Customization IDs are media hashed IDs.

### Payloads and exact field names

Factories reuse the existing [media](media.md), [folder/subfolder](folders.md), [channel/episode](channels.md) request validators/JSON encoders and the ten [typed customization update inputs](customizations.md). Only body fields are accepted. For bulk creation, `createSubfolder` adds required `folder_id` and `createChannelEpisode` adds required `channel_id` from their corresponding endpoint path contexts. Episode publishing/scheduling rules and typed podcast settings are retained.

```php
use Automattic\Wistia\Customizations\UpdateAppearanceCustomizationsInput;

$appearance = Change::updateAppearance(new UpdateAppearanceCustomizationsInput(
    playerColor: null, // Explicit reset; omitted fields remain unchanged.
    branding: false,
));
$metadata = Change::updateMedia(custom_metadata: (object) ['topic' => null, 'rating' => 0]);
$captions = Change::createCaptions(
    media_id: 'media-hash',
    caption_file: "1\n00:00:00,000 --> 00:00:01,000\nExample\n",
    language: 'en',
);
```

Customization changes accept only the corresponding `Update…CustomizationsInput`, preserving nested objects, mixed-case wire keys, omission and explicit-null rules. Broad customization replacement is unavailable in bulk. Lead capture retains its required provider. Media custom metadata is a JSON object: a null value clears that key, omitted keys remain unchanged. Account custom-metadata feature access and field-specific value validation remain server responsibilities, as in the single-media update contract; failures affect individual actions.

Bulk captions carry **inline SRT text**, not a multipart file or local file path. Creation requires `media_id` and `caption_file`; `language` is optional and detected remotely when omitted. Creating an existing language replaces its track. Updates take only `caption_file`, because the track fixes its language. Caption content/language eligibility is validated remotely. Caption purchases, localization generation, translation, audio-description orders, bulk purchases and account trials are not exposed by these factories.

`moveMedia` takes one destination `folder_id` and optional `subfolder_id`; omit the latter to move to the folder root. The subfolder must belong to that folder (remote validation). Each action can use a different destination. The separate Move Media endpoint's 100-media cap/throttle does not apply here.

## One change across many records

```php
use Automattic\Wistia\Bulk\{Job, Scope, ScopeType};

$scoped = new CreateBulkActionsRequest(job: new Job(
    change: Change::updateMedia(name: 'Shared title'),
    scope: new Scope(ScopeType::Folder, 'folder-hash'),
));
$explicit = new CreateBulkActionsRequest(job: new Job(
    change: Change::moveMedia(folder_id: 'destination-folder'),
    ids: ['first-media', 'second-media'],
));
```

Provide exactly one of `actions` or `job`, and exactly one job `scope` or `ids`. Jobs cannot create records. `ScopeType::Account` has no ID; all other scope types require a nonempty parent ID. Allowed parents:

| Job resource | Scope types |
| --- | --- |
| Media and every customization concern | Account, Folder, Subfolder, Channel |
| Captions | Account, Media, Folder, Channel |
| ChannelEpisode | Account, Channel, Media |
| Subfolder | Account, Folder |
| Folder, Channel | Account |

Invalid combinations are rejected before transport. A scope resolves using its matching list endpoint's defaults: folder-scoped media includes subfolders **and archived media**. Scope membership/count and authorization are remote facts, not locally enumerated by this library. Jobs are unavailable to external contacts.

## Limits, results and failures

- Explicit actions: a list of 1–1000 typed actions. Explicit job IDs: a list of 1–1000 nonempty strings.
- The full encoded JSON body must stay **under 2 MB**. The library conservatively uses decimal 2 MB: fewer than 2,000,000 bytes, including JSON escaping/overhead. The server may also reject oversized bodies with 413; no action in such a rejected body runs.
- A scoped job resolves to at most 5000 records, enforced by Wistia. Larger scopes are rejected, not truncated; narrow the scope or submit explicit actions. The library cannot know scope size without a remote read.
- Payload JSON is snapshotted when a Change is constructed; the complete body is frozen when the request is constructed. Later mutation of supplied metadata maps cannot bypass validation or size checks.

Submission returns immediately without polling. Use its hashed ID with [Show Background Job Status and finite optional polling](pagination.md#optional-finite-polling). The shared result exposes aggregate `summary` and per-action `results`, including created record IDs and individual errors. Each action is authorized/processed independently; one failure does not stop others. A finished job can have failures: check `hasFailures()` or the poller's `ReportedFailure`, not just status `finished`. Stopping local polling does not cancel remote work.

The [shared failure contract](foundation.md#results-and-failures) applies: sanitized API/authentication/permission/rate-limit/transport/decoding exceptions, supplied `Retry-After`, single-attempt execution and no automatic retries. A disconnect, timeout, server failure or malformed success after submission may leave the remote outcome uncertain; reconcile rather than replaying the batch. Per-action error strings are untrusted/sensitive and are only exposed for deliberate application handling, not debug output.

Requires **Read, update & delete anything** or delegated team-member permissions (authorized as the assigned contact). Expiring tokens with authorizations cannot submit bulk requests: the token's authorizations are not propagated into the background job, so Wistia forbids them. The library does not infer token permissions from opaque token bytes. Plan/feature access remains remote. No live/destructive checks, purchases or credentials are enabled by offline contract coverage.
