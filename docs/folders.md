# Folders, sharing and subfolders

P09 implements 17 modern Data operations through `DataFoldersService`, `DataFolderSharingsService` and `DataSubfoldersService`. Construct each service around a configured [Client](foundation.md). Named requests live in `Request\Data`, results in `Result\Data`, and nested records and sharing inputs in `Folders`. The [Data reference](reference/data.md#folders-sharing-and-subfolders) maps every operation to its method and offline contract test.

## Read folders and subfolders

```php
use Automattic\Wistia\{DataFoldersService, DataSubfoldersService};
use Automattic\Wistia\Request\Data\{ListFoldersRequest, ShowSubfolderRequest, Cursor};

$folders = new DataFoldersService($client);
$page = $folders->listFolders(new ListFoldersRequest(per_page: 20));
foreach ($page->data as $folder) {
    // Typed fields, including hashed_id, kind, personal_library and medias->url.
}

$subfolders = new DataSubfoldersService($client);
$subfolder = $subfolders->showSubfolder(new ShowSubfolderRequest(
    folderId: 'folder-id', subfolderId: 'subfolder-id', description_format: 'markdown',
));
// $subfolder->data->medias contains typed media and thumbnail records.

$traversal = $folders->paginateFolders(new ListFoldersRequest(
    per_page: 20, cursor: new Cursor(enabled: 1), sort_by: 'id', sort_direction: 0,
), maxPages: 10);
foreach ($traversal->items() as $folder) {
    // Application-owned processing; stopping iteration stops further reads.
}
```

`listFolders`, `listFolderSharings` and `listSubfolders` fetch individual pages. Their `paginateFolders`, `paginateFolderSharings` and `paginateSubfolders` companions provide lazy bounded `page()`, `pages()` and `items()` access. They preserve parent identifiers, `hashed_ids[]`, sorting and page size, and reject repeated cursors rather than looping. Offset traversal starts at the supplied page; cursor traversal preserves its before/after direction. Do not mix `page` and `cursor`. Generic traversal page sizes are 1–100; single-page requests require positive values and leave undocumented endpoint upper bounds to Wistia.

Folder cursor sorting supports `id`, `updated` and `created`. Sharing and subfolder cursor sorting supports only `id`. When an active subfolder cursor omits `sort_by`, the request sends `id` instead of the incompatible server default `position`; offset requests retain the server default. Offset sorting retains each endpoint's documented choices. Keep sorting unchanged when reusing a cursor. See [pagination and polling](pagination.md) for exhaustion and continuation semantics.

## Write inputs and results

Folder create/update bodies preserve `adminEmail`, `anonymousCanUpload`, `anonymousCanDownload`, `personalLibrary` and `public` exactly where supported. Results retain the distinct response names (`anonymous_can_upload`, `personal_library`, `media_count`, `public_id`), rather than blanket-normalizing case. Folder creation may omit all properties and use server defaults. A copy defaults its new owner to the account owner when `adminEmail` is invalid or omitted.

Subfolder names have a 255-character limit and descriptions a 1000-character limit, checked as UTF-8 characters before execution. A required name may be empty because the retained schema supplies no minimum length. `description` distinguishes omission from explicit `null` and an empty string:

```php
use Automattic\Wistia\Request\Data\UpdateSubfolderRequest;

$unchanged = new UpdateSubfolderRequest('folder-id', 'subfolder-id');
$reset = new UpdateSubfolderRequest('folder-id', 'subfolder-id', description: null);
$empty = new UpdateSubfolderRequest('folder-id', 'subfolder-id', description: '');
// Pass the selected request to DataSubfoldersService::updateSubfolder explicitly.
```

Optional nonnullable inputs use PHP `null` for omission. Nullable description inputs default to `Folders\Omitted::Value`. Response records retain `presentFields` so callers can distinguish omitted properties from nulls. List results are bare arrays of typed records; folder `medias` is a URL wrapper, whereas show-subfolder `medias` is a media list. Create-sharing alone returns a `project` URL and nested `sharing`, not a bare sharing record. Normal debug views hide payloads; explicitly reading or serializing fields still discloses application data.

### Sharing payloads and notifications

```php
use Automattic\Wistia\DataFolderSharingsService;
use Automattic\Wistia\Folders\{CreateSharing, UpdateSharing};
use Automattic\Wistia\Request\Data\{CreateFolderSharingRequest, UpdateFolderSharingRequest};

$sharings = new DataFolderSharingsService($client);
$create = new CreateFolderSharingRequest('folder-id', new CreateSharing(
    with: 'colleague@example.test', requirePassword: true, canDownload: false,
));
$update = new UpdateFolderSharingRequest('folder-id', 'sharing-id', new UpdateSharing(
    canShare: false, canUpload: false, isAdmin: false,
));
// Submitting $create sends an email notification; constructing it does not.
```

Sharing flags remain nested under `sharing` and preserve explicit false. The deprecated string flag `sendEmailNotification` accepts only `"0"` or `"1"`; **notifications are always sent**, even with `"0"`. Omitting `sharing` in an update sends `{}`; an empty `UpdateSharing` sends `{"sharing":{}}`. Show-sharing takes an integer `sharingId` per its source schema; update/delete take strings. All path segments are encoded individually, including nested identifiers.

## Destructive operations and asynchronous jobs

**Do not execute destructive calls without separate approval.** Deleting a folder removes its contents and loses associated stats. Deleting one subfolder moves its media to the parent folder's root, then soft-deletes the subfolder. Bulk deleting subfolders also soft-deletes their media; an account owner or manager can restore media from trash until purged. An empty bulk identifier list remains an explicit JSON array, not an omitted body. Sharing deletion revokes access.

Folder copy returns HTTP **202** with a bare job record at `$copy->data` (`id`, `hashed_id`, `status`). Bulk subfolder deletion returns HTTP **200** with `$bulk->data->background_job_status` plus `message`. The bulk call sends `{"hashed_ids":[...]}` as a JSON **DELETE body**. Neither method waits for remote completion or replays the write.

To follow either job, pass its `hashed_id` to `Request\Data\ShowBackgroundJobStatusRequest`, then use `DataBackgroundJobStatusService` or the existing finite `Jobs\BackgroundJobPoller`. Completion, failure, partial-action failures and exhaustion follow the [shared polling contract](pagination.md#optional-finite-polling). Stopping polling does not cancel the remote operation. Transport errors expose `remoteOutcomeUncertain` for writes; reconcile the remote state before considering another attempt.

## Permissions and verification limits

All operations use Bearer authentication, `/modern` and API version `2026-09`. Read operations generally require read-all-folder/media permission; writes require read/update/delete-anything permission. Delegated-contact tokens are authorized as their assigned contact; resource-scoped expiring tokens need the documented folder permission. For folder-scoped expiring tokens, listing/showing sharings accepts any permission on the named folder, creating a sharing requires `share`, and updating/deleting a sharing requires `update`. Subfolder writes require `update`. Show-folder and show-subfolder may restrict embedded media to the token's authorizations. Folder copy and bulk subfolder deletion are unavailable to **Granted Access (folder-level) tokens**. Personal-library folders belong to the requesting user instead of the shared account. Exact per-operation restrictions and source hashes are retained in the Data reference and [inventory](rewrite/operations.json).

Offline synthetic tests cover all 17 request/response contracts, nested IDs, flags and wrappers, DELETE bodies, local validation, cursor/offset traversal, sanitized errors, and single-attempt uncertain writes. Live API behavior, account entitlement and paid-plan availability remain unverified; absent plan restrictions are not proof of free-plan support. Other resource endpoints, charge-incurring operations, trials and Upload workflows remain outside P09.

For migration, replace old project magic calls such as `list_projects()` with `DataFoldersService::listFolders(new ListFoldersRequest(...))` and typed results. Subfolders and sharings have separate explicit services. There is no compatibility layer; modern Data uses folders, while Upload/Stats retain their own documented project names.
