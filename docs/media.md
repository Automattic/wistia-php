# Media lifecycle and organization

The five services in P06 implement 19 Data operations: `DataMediaService`, `DataTrimsService`, `DataTagsService`, `DataTaggingsService`, and `DataDeletedMediaService`. Construct them directly around a configured `Client`. Each method takes its named `Request\Data` object and returns its named `Result\Data` object; the [operation reference](reference/data.md) lists inputs, success statuses and restrictions. These operations use Bearer authentication and pinned API version `2026-09` on `https://api.wistia.com/modern`.

## List media without live requests

This runnable example uses synthetic responses and does not contact Wistia:

```php
<?php
require 'vendor/autoload.php';

use Automattic\Wistia\{Client, Configuration, DataMediaService};
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Request\Data\{Cursor, ListMediaRequest};

$transport = new class implements Transport {
    private int $reads = 0;
    public function send(TransportRequest $request): TransportResponse {
        $body = ++$this->reads === 1
            ? '[{"folder":null,"hashed_id":"synthetic-media","name":"Example","cursor":"next-page"}]'
            : '[]';
        return new TransportResponse(200, ['Content-Type' => ['application/json']], $body);
    }
};
$service = new DataMediaService(new Client(new Configuration('synthetic-token'), $transport));
$pages = $service->paginateMedia(new ListMediaRequest(
    per_page: 1, cursor: new Cursor(enabled: 1), sort_by: 'id', tags: ['example'], archived: false,
), maxPages: 3);
foreach ($pages->items() as $media) {
    echo $media->name, PHP_EOL;
}
```

`listMedia`, `listTags` and `listDeletedMedia` fetch one page. Their lazy `paginateMedia`, `paginateTags` and `paginateDeletedMedia` companions use the existing [pagination helpers](pagination.md), keeping filters and sorting on each read. `Paginator::page(position)` fetches an individual page; `pages()`/`items()` stop at a short or empty page, a cursor cycle, a request failure or the finite page bound. Full final pages need a subsequent empty read. Breaking out of iteration sends no further requests.

Supply `page` for offset mode, or `Cursor(enabled: 1)`, `Cursor(after: 'opaque')` or `Cursor(before: 'opaque')` for cursor mode. They cannot be mixed. Cursors are raw values, encoded once. `Cursor(enabled: 0)` keeps offset mode. Media cursor sorting permits only `id`/`created`; offset mode permits `name`/`created`/`updated`/`position`. This follows the source narrative despite its enum omitting cursor `id`. Tag cursor mode permits `id`/`updated`/`created`; its other sorts require offset mode. Deleted-media sorts follow its own contract. Traversal helpers accept page sizes 1–100, a local helper bound; single-page requests require a positive size without inventing an undocumented endpoint maximum.

## Read results and preserve wire names

Each P06 result has `data` and `status`. `data` is a typed record, a list of typed records, or null for schema-less Delete Tag. Record properties retain exact wire spelling, including `embedCode`, `hashed_id`, `file_size`, `pageLoads` and container `hashedId`. Nested assets, speakers, tags, folder, subfolder, thumbnail, jobs and move errors are typed records under `Media`. The result's declared type and source files identify each concrete record.

Optional properties become PHP null when absent. Each record's `presentFields: list<string>` distinguishes absence from an explicit nullable wire field; an explicit null on a non-nullable field fails decoding. For example, a missing `protected` and `protected: null` both produce `$record->protected === null`, but only the second includes `protected` in `presentFields`. Required fields, nested list/object distinctions, scalar types, documented enums, numeric ranges and closed-object keys are validated. Malformed responses are decoding failures, not empty successful records. Source-described nullable subfolders are accepted despite the generated object schema's nullable omission.

Debug views omit payloads. Deliberate property access or serialization can still disclose media metadata, signed URLs, cursors, errors and job details; applications own safe handling and logging.

## Update, copy, tag and trim

- `UpdateMediaRequest(mediaHashedId, ...)` updates only supplied non-null fields. Empty `name`/`description` strings and `tags: []` are sent, not omitted. `tags` replaces existing tags. Use `custom_metadata: (object) ['field_key' => null]` to clear an individual metadata value; an omitted key stays unchanged. `(object) []` is an empty JSON object, not a list. This feature requires account entitlement. The outer request's null arguments mean omission; the endpoint does not document outer null clearing.
- `CopyMediaRequest` takes the source hashed ID but an optional **integer** `folder_id`; its owner is an email string. Invalid/omitted folder or owner falls back remotely to the source values. Bulk copy instead requires a **hashed string** `folder_id`; it returns a job, not a media list. Keep these distinct.
- `SwapMediaRequest` preserves the original hashed ID and URLs while queuing replacement. Replacement must be the same media type; ownership/type/authorization checks remain remote.
- `BulkTagMediaRequest` adds tags without replacing existing ones. `CreateTagsRequest` creates a tag whose stored name is lowercased and whitespace-squished by Wistia, limited to 50 characters. The library validates that size but sends original bytes. Duplicate names remain remote errors. Delete Tag documents status 200 without a body schema; its result records status and deliberately discards the unspecified body instead of claiming a JSON or empty-body contract.
- Trims support **video only**. Pass a nonempty list of `HH:MM:SS.mmm-HH:MM:SS.mmm` ranges with each end after its start. `keep_trims: false` (or omission) removes ranges; true keeps them. Local syntax/order checks do not establish remote duration/type validity. The response exposes a job, not a completed media result.

## Follow asynchronous work

Modern URL import, swap, archive, move, archived restore, bulk copy/tagging, trims and deleted-media restoration expose job identifiers immediately. For example, after an authorized archive call:

```php
// $created = $mediaService->archiveMedia(new ArchiveMediaRequest(['hashed-id']));
// $id = $created->data->background_job_status->hashed_id;
// Pass $id to ShowBackgroundJobStatusRequest and the existing BackgroundJobPoller.
```

Use [finite optional polling](pagination.md#optional-finite-polling) to perform status reads. The creation operation is never replayed. A returned job is not completion; a finished bulk job can still contain action failures. Stopping local polling does not cancel remote work. Deleted-media restoration's source schema has optional job fields: check that a nonempty hashed ID or numeric ID is present before choosing to poll; the library does not invent missing identifiers.

Move accepts both 200 and **207**. Check `result->status` first: only the 207 result variant has `result->data->partial_errors`; inspect it even when a job was queued, using `partial_errors ?? []` because the field is optional. A 200 result has no `partial_errors` property. Errors retain their media IDs and messages for deliberate reconciliation. Move is limited to 100 media and 10 requests per five minutes, separately from the general rate limit; the optional subfolder must belong to the destination folder. Archive and archived restore also cap requests at 100. Archived restore requires the Archiving feature. Webinar media and pre-September-2023 imported Soapbox videos cannot be archived. These entitlement/media-history rules remain remote checks.

Modern URL import uses JSON on the Data host and returns 201/job status; it is not the separately planned form-encoded Upload-host workflow. The URL must be publicly accessible; Wistia rejects certain domains, including vimeo.com and wistia.com. Omitting `folder_id` creates an “Untitled Folder.” Local validation checks absolute HTTP(S) URI syntax, including percent escapes, component characters and IP literals, and rejects embedded credentials. Percent-encode non-URI characters (including UTF-8 bytes) before constructing the request. Validation preserves valid signed URL bytes exactly; it does not fetch the URL or verify accessibility, ownership or domain policy.

## Deleted versus archived media

Deleting media moves it to Recently Deleted. Restoration is available only during the account's restore window (30 days on most plans, 14 on free plans); later purge is permanent. `listDeletedMedia` lists only still-restorable records and preserves `deleted_at`/`restore_expires_at` as strings. `RestoreDeletedMediaRequest` uses `media_hashed_ids` (up to 1000), not archive's `hashed_ids`; it returns 202/job status. Without a destination folder, media return to their previous folders. This is separate from archived-media `restoreMedia`, which requires a destination and Archiving entitlement.

## Authorization, failures and evidence

The [per-operation reference](reference/data.md) retains exact permission/delegation and expiring-token restrictions. Tokens never imply account entitlement. Bulk tagging rejects restricted expiring authorizations; move forbids `subfolder_id` for expiring tokens. Copy fallback, deleted-media window eligibility, metadata entitlement and remote processing errors stay distinguishable from locally invalid input.

All requests use the [shared failure contract](foundation.md#results-and-failures): sanitized authentication, permission, API, rate-limit, transport and decoding exceptions. Rate-limit metadata remains accessible. Requests are attempted once, without automatic retries or redirects. Ambiguous write failures mark the remote outcome uncertain, including transport loss, server errors and malformed successful write responses; reconcile before submitting another mutation.

Offline synthetic contract tests cover all 19 methods, encoding, statuses, nested results, shared failures/no replay, validation, pagination and optional polling handoff. They do not verify Wistia's live behavior, plan access or destructive operations. No live Wistia call or paid-plan access is claimed. Upload workflows, customizations, localization, purchases and other resource services remain outside P06.
