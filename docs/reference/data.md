# Implemented Data API reference

This page covers P03's job-status operation, P06's 19 media lifecycle, tags, trims and deleted-media operations, P07's 24 customization operations, P08's 16 captions/accessibility operations, P09's 17 folder/sharing/subfolder operations, and P10's 16 channel/collaborator/episode operations. Other Data mappings in the [inventory](../rewrite/operations.json) remain planned; this page does not claim full Data API coverage. See the [media guide](../media.md) for usage, result conventions, pagination, polling and shared restrictions. Construct services with the shared `Client` directly, not a client accessor or magic dispatch.

## get_background-job-status-backgroundjobstatusid

**API:** `DataBackgroundJobStatusService::showBackgroundJobStatus(Request\Data\ShowBackgroundJobStatusRequest): Result\Data\ShowBackgroundJobStatusResult`.

**Wire:** `GET https://api.wistia.com/modern/background_job_status/{backgroundJobStatusId}`, `Authorization: Bearer …`, `X-Wistia-API-Version: 2026-09`. No query or body. The request takes a nonempty raw string `backgroundJobStatusId` (hashed or numeric ID); it is encoded once as a path segment. Prefer the unguessable hashed ID for polling. No request is made during service construction. Only status 200/application-json-compatible content is decoded as success.

**Source:** [Show Background Job Status](https://docs.wistia.com/reference/get_background-job-status-backgroundjobstatusid.md), checked P01 source SHA-256 `0d4471b3046472564fb50a0bbdf61bd7c5cc2aaa289673076e64cb034a91604e`. Source hashes pin documentation bytes, not server behavior. This is not marked experimental in the checked source; that is not a stability guarantee.

### Typed response

`ShowBackgroundJobStatusResult::backgroundJobStatus` is a `Jobs\BackgroundJobStatus`:

| Wire field | PHP field/type | Requirement |
| --- | --- | --- |
| `id` | `id: int` | Required integer. |
| `hashed_id` | `hashedId: string` | Required. |
| `status` | `status: Jobs\JobStatus` | Required: `Queued`, `Started`, `Finished`, `Failed` (wire lowercase). |
| `object` | `object: ?Jobs\BulkJobContent` | Optional, not wire-nullable; absent becomes null. Only documented discriminator `type=bulk_operation` is supported. |

Bulk content has required `summary: Jobs\BulkSummary` (`total`, `succeeded`, `failed` integers) and `results: list<Jobs\BulkActionResult>` in submission order, initially possibly empty. `type` is represented by the concrete `BulkJobContent` class rather than a mutable string. Each action contains required `position: int`, `status: Jobs\BulkActionStatus` (`Succeeded`/`Failed`); optional `operation: ?Jobs\BulkOperation` (`Create`, `Update`, `Delete`, `Move`, `Purchase`), `resourceType: ?string` (wire `resource_type`), `id: ?string`, and `error: ?string`. Optional missing fields become null; explicit wire null is rejected. A failed create may have no ID. `error` is untrusted sensitive text available for deliberate application handling, never interpolated into library exception messages or debug views. Reading the documented `purchase` label does not implement or authorize bulk purchases.

`BackgroundJobStatus::hasFailures()` also checks positive bulk failed counts and per-action failed statuses. A finished job does not imply every action succeeded. The decoder validates every required/optional field, list/object distinction, enum and closed-object property set from the pinned success schema; malformed/unknown shapes fail rather than becoming empty successes. No undocumented count consistency, positivity or relationship constraints are invented.

### Restrictions and failures

Requires a token with **Read all data**, or delegated team-member permissions authorized for the assigned contact. An expiring `all:delegate_to_contact_permissions` token must cover the job's record (media/folder/channel/webinar); any permission on that record suffices. An account job is covered by any token for that account. Jobs on other records, including bulk operations, are not found with those restricted authorizations. No account/feature entitlement or live access is implied.

401 produces `AuthenticationException`; 404/500 produce shared `ApiException`. Other statuses retain the [shared failure contract](../foundation.md#results-and-failures), including 429/rate-limit metadata and sanitized transport/decoding failures. Only the documented 200 success is allowed. Status reads are read-only, are sent once and never retried; they do not replay writes or cancel jobs. Failures do not prove that the original asynchronous operation did nothing.

### Runnable synthetic service example

This example executes no HTTP and uses no credentials. Run from the repository root:

```php
<?php
require 'vendor/autoload.php';

use Automattic\Wistia\{Client, Configuration, DataBackgroundJobStatusService};
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Jobs\{BackgroundJobPoller, PollingOptions, PollingTermination};
use Automattic\Wistia\Request\Data\ShowBackgroundJobStatusRequest;

$transport = new class implements Transport {
    public function send(TransportRequest $request): TransportResponse {
        return new TransportResponse(200, ['Content-Type' => ['application/json']],
            '{"background_job_status":{"id":42,"hashed_id":"synthetic-job","status":"finished"}}');
    }
};
$service = new DataBackgroundJobStatusService(new Client(new Configuration('synthetic-token'), $transport));
$request = new ShowBackgroundJobStatusRequest('synthetic-job');
$job = $service->showBackgroundJobStatus($request)->backgroundJobStatus;
$result = (new BackgroundJobPoller($service))->poll($request, new PollingOptions(maxAttempts: 1));
if ($job->hashedId !== 'synthetic-job' || $result->termination !== PollingTermination::Completed) {
    throw new RuntimeException('Unexpected job status.');
}
```

See [pagination and polling](../pagination.md#optional-finite-polling) for bounded traversal, termination and consumer responsibilities. Consumers own safe handling of job/action data, reconciliation, persistence and orchestration. Stopping polling never cancels remote work.

### Evidence

`tests/Contract/Data/ShowBackgroundJobStatusTest.php` uses authored synthetic responses, not copied generated SDK fixtures. It covers Bearer/version/path encoding, typed nested bulk results, all statuses, malformed shapes/content, unexpected success statuses and sanitized shared errors. Offline polling tests cover fake time/waits and partial failures. PHP 8.4/8.5 network-denied Composer runs are retained in delivery evidence. No live account, paid-plan behavior, remote purchase/generation operation or independent acceptance is claimed here.

## get_medias

**API:** `DataMediaService::listMedia(Request\Data\ListMediaRequest): Result\Data\ListMediaResult`.

**Wire:** `GET /medias`; Bearer token and version `2026-09` on the modern Data host. Path identifiers are raw strings encoded once.

| Input | Type | Location / requirement |
| --- | --- | --- |
| `page` | `integer` | query; optional |
| `per_page` | `integer` | query; optional |
| `cursor` | `Cursor` | query; optional |
| `sort_by` | `string` | query; optional |
| `sort_direction` | `integer` | query; optional |
| `folder_id` | `string` | query; optional |
| `name` | `string` | query; optional |
| `description_format` | `string` | query; optional |
| `include` | `string` | query; optional |
| `type` | `string` | query; optional |
| `hashed_ids` | `list<string>` | query; optional; wire `hashed_ids[]` |
| `tags` | `list<string>` | query; optional; wire `tags[]` |
| `archived` | `boolean` | query; optional |

**Success:** 200 JSON. The named result exposes typed `data` and HTTP `status`; see its [PHP declaration](../../src/Result/Data/ListMediaResult.php).

**Restrictions:** Lists the media belonging to the account. This endpoint can also be used to
do a batch fetch based off of the hashed id.

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

**Source/evidence:** [List Media](https://docs.wistia.com/reference/get_medias.md), pinned SHA-256 `137b62afb18e37506bfeadb6c1158a7a55eb2c232ef91b8f5711c088d83d3423` in the inventory. Not marked experimental in the checked source. Synthetic offline contract: `tests/Contract/Data/ListMediaTest.php` (shared cases/assertions in `MediaCases`/`MediaOperationContract`, plus `tests/Offline/MediaLifecycleTest.php`). No live or plan-entitlement verification.


## get_medias-mediahashedid

**API:** `DataMediaService::showMedia(Request\Data\ShowMediaRequest): Result\Data\ShowMediaResult`.

**Wire:** `GET /medias/{mediaHashedId}`; Bearer token and version `2026-09` on the modern Data host. Path identifiers are raw strings encoded once.

| Input | Type | Location / requirement |
| --- | --- | --- |
| `mediaHashedId` | `string` | path; required |
| `description_format` | `string` | query; optional |
| `include` | `string` | query; optional |

**Success:** 200 JSON. The named result exposes typed `data` and HTTP `status`; see its [PHP declaration](../../src/Result/Data/ShowMediaResult.php).

**Restrictions:** Fetches a single media by its hashed id.

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization for this media can also be used; any permission granted on a
media allows showing it.

**Source/evidence:** [Show Media](https://docs.wistia.com/reference/get_medias-mediahashedid.md), pinned SHA-256 `20ebb176bb47db48b372ab2fea5723aea89634c8ba52e50009d66010b6b06a1a` in the inventory. Not marked experimental in the checked source. Synthetic offline contract: `tests/Contract/Data/ShowMediaTest.php` (shared cases/assertions in `MediaCases`/`MediaOperationContract`, plus `tests/Offline/MediaLifecycleTest.php`). No live or plan-entitlement verification.


## put_medias-mediahashedid

**API:** `DataMediaService::updateMedia(Request\Data\UpdateMediaRequest): Result\Data\UpdateMediaResult`.

**Wire:** `PUT /medias/{mediaHashedId}`; Bearer token and version `2026-09` on the modern Data host. Path identifiers are raw strings encoded once.

| Input | Type | Location / requirement |
| --- | --- | --- |
| `mediaHashedId` | `string` | path; required |
| `name` | `string` | JSON body; optional |
| `new_still_media_id` | `string` | JSON body; optional |
| `description` | `string` | JSON body; optional |
| `tags` | `list<string>` | JSON body; optional |
| `custom_metadata` | `stdClass (open metadata map)` | JSON body; optional |

**Success:** 200 JSON. The named result exposes typed `data` and HTTP `status`; see its [PHP declaration](../../src/Result/Data/UpdateMediaResult.php).

**Restrictions:** Updates the attributes on a media.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `update` permission on this media can also be
used.

**Source/evidence:** [Update Media](https://docs.wistia.com/reference/put_medias-mediahashedid.md), pinned SHA-256 `745af2a3bbd35f1e9d7232fd08f119418f3eac3847f099e2744ec144af5468dc` in the inventory. Not marked experimental in the checked source. Synthetic offline contract: `tests/Contract/Data/UpdateMediaTest.php` (shared cases/assertions in `MediaCases`/`MediaOperationContract`, plus `tests/Offline/MediaLifecycleTest.php`). No live or plan-entitlement verification.


## delete_medias-mediahashedid

**API:** `DataMediaService::deleteMedia(Request\Data\DeleteMediaRequest): Result\Data\DeleteMediaResult`.

**Wire:** `DELETE /medias/{mediaHashedId}`; Bearer token and version `2026-09` on the modern Data host. Path identifiers are raw strings encoded once.

| Input | Type | Location / requirement |
| --- | --- | --- |
| `mediaHashedId` | `string` | path; required |

**Success:** 200 JSON. The named result exposes typed `data` and HTTP `status`; see its [PHP declaration](../../src/Result/Data/DeleteMediaResult.php).

**Restrictions:** Deletes a media. Deleted media moves to the account's Recently Deleted area,
where it can be restored until the account's restore window ends, after which
it is permanently purged.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `destroy` permission on this media can also be
used.

**Source/evidence:** [Delete Media](https://docs.wistia.com/reference/delete_medias-mediahashedid.md), pinned SHA-256 `b83451f4372ed7e70b27dfd9e0796167b22486871374d01da50c9e6f0d713324` in the inventory. Not marked experimental in the checked source. Synthetic offline contract: `tests/Contract/Data/DeleteMediaTest.php` (shared cases/assertions in `MediaCases`/`MediaOperationContract`, plus `tests/Offline/MediaLifecycleTest.php`). No live or plan-entitlement verification.


## post_medias-mediahashedid-copy

**API:** `DataMediaService::copyMedia(Request\Data\CopyMediaRequest): Result\Data\CopyMediaResult`.

**Wire:** `POST /medias/{mediaHashedId}/copy`; Bearer token and version `2026-09` on the modern Data host. Path identifiers are raw strings encoded once.

| Input | Type | Location / requirement |
| --- | --- | --- |
| `mediaHashedId` | `string` | path; required |
| `folder_id` | `integer` | JSON body; optional |
| `owner` | `string` | JSON body; optional |

**Success:** 201 JSON. The named result exposes typed `data` and HTTP `status`; see its [PHP declaration](../../src/Result/Data/CopyMediaResult.php).

**Restrictions:** This endpoint copies a media and its assets to a destination folder (defaults to source media).

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

**Source/evidence:** [Copy Media](https://docs.wistia.com/reference/post_medias-mediahashedid-copy.md), pinned SHA-256 `0f07c8ea487386f0ecc7da7eb6614df9b667de7950415806435f791b5f25f26f` in the inventory. Not marked experimental in the checked source. Synthetic offline contract: `tests/Contract/Data/CopyMediaTest.php` (shared cases/assertions in `MediaCases`/`MediaOperationContract`, plus `tests/Offline/MediaLifecycleTest.php`). No live or plan-entitlement verification.


## put_medias-mediahashedid-swap

**API:** `DataMediaService::swapMedia(Request\Data\SwapMediaRequest): Result\Data\SwapMediaResult`.

**Wire:** `PUT /medias/{mediaHashedId}/swap`; Bearer token and version `2026-09` on the modern Data host. Path identifiers are raw strings encoded once.

| Input | Type | Location / requirement |
| --- | --- | --- |
| `mediaHashedId` | `string` | path; required |
| `replacement_media_id` | `string` | JSON body; required |

**Success:** 200 JSON. The named result exposes typed `data` and HTTP `status`; see its [PHP declaration](../../src/Result/Data/SwapMediaResult.php).

**Restrictions:** Swap one media with another media. This operation queues a background job to replace the original media with the replacement media while preserving the original media's hashed ID and URLs.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope can also be
used when its authorizations grant the `update` permission on both the
media being replaced and the replacement media. A replacement media the
token does not name is treated as not found.

**Source/evidence:** [Swap Media](https://docs.wistia.com/reference/put_medias-mediahashedid-swap.md), pinned SHA-256 `5a62ce17971f9589d500180db3bfc2ea4650114ebf00c88f4284679397888610` in the inventory. Not marked experimental in the checked source. Synthetic offline contract: `tests/Contract/Data/SwapMediaTest.php` (shared cases/assertions in `MediaCases`/`MediaOperationContract`, plus `tests/Offline/MediaLifecycleTest.php`). No live or plan-entitlement verification.


## get_medias-mediahashedid-stats

**API:** `DataMediaService::showMediaAggregatedStats(Request\Data\ShowMediaAggregatedStatsRequest): Result\Data\ShowMediaAggregatedStatsResult`.

**Wire:** `GET /medias/{mediaHashedId}/stats`; Bearer token and version `2026-09` on the modern Data host. Path identifiers are raw strings encoded once.

| Input | Type | Location / requirement |
| --- | --- | --- |
| `mediaHashedId` | `string` | path; required |

**Success:** 200 JSON. The named result exposes typed `data` and HTTP `status`; see its [PHP declaration](../../src/Result/Data/ShowMediaAggregatedStatsResult.php).

**Restrictions:** Aggregated tracking statistics for a video embedded on your site.

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

**Source/evidence:** [Show Media Aggregated Stats](https://docs.wistia.com/reference/get_medias-mediahashedid-stats.md), pinned SHA-256 `bcb52b0f7134f44a06906111b678f833734111c03598c9ee042daf1e2d0a08fd` in the inventory. Not marked experimental in the checked source. Synthetic offline contract: `tests/Contract/Data/ShowMediaAggregatedStatsTest.php` (shared cases/assertions in `MediaCases`/`MediaOperationContract`, plus `tests/Offline/MediaLifecycleTest.php`). No live or plan-entitlement verification.


## post_medias-import-url

**API:** `DataMediaService::importMediaFromURL(Request\Data\ImportMediaFromURLRequest): Result\Data\ImportMediaFromURLResult`.

**Wire:** `POST /medias/import_url`; Bearer token and version `2026-09` on the modern Data host. Path identifiers are raw strings encoded once.

| Input | Type | Location / requirement |
| --- | --- | --- |
| `url` | `string` | JSON body; required |
| `folder_id` | `string` | JSON body; optional |

**Success:** 201 JSON. The named result exposes typed `data` and HTTP `status`; see its [PHP declaration](../../src/Result/Data/ImportMediaFromURLResult.php).

**Restrictions:** This endpoint imports a media file from a given URL. The import is processed
asynchronously and will return a background_job_status object rather than the
typical Media response object. You can poll the background job status endpoint
to check on the progress of the import.

If no folder_id is provided, a new folder called "Untitled Folder" will be
created and the imported media will be placed there.

The URL must be publicly accessible — Wistia's servers need to be able to fetch the file directly.

Note: imports from certain domains (e.g. vimeo.com, wistia.com) are not permitted.


## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

**Source/evidence:** [Import Media from URL](https://docs.wistia.com/reference/post_medias-import-url.md), pinned SHA-256 `d45cc79462fb394f9de6d51c0298b7e67518a506e43c883437065da242fb8518` in the inventory. Not marked experimental in the checked source. Synthetic offline contract: `tests/Contract/Data/ImportMediaFromURLTest.php` (shared cases/assertions in `MediaCases`/`MediaOperationContract`, plus `tests/Offline/MediaLifecycleTest.php`). No live or plan-entitlement verification.


## put_medias-archive

**API:** `DataMediaService::archiveMedia(Request\Data\ArchiveMediaRequest): Result\Data\ArchiveMediaResult`.

**Wire:** `PUT /medias/archive`; Bearer token and version `2026-09` on the modern Data host. Path identifiers are raw strings encoded once.

| Input | Type | Location / requirement |
| --- | --- | --- |
| `hashed_ids` | `list<string>` | JSON body; required |

**Success:** 200 JSON. The named result exposes typed `data` and HTTP `status`; see its [PHP declaration](../../src/Result/Data/ArchiveMediaResult.php).

**Restrictions:** This method accepts a list of up to 100 medias to archive per request. It processes requests asynchronously and will return a background_job_status object rather than the typical Media response object. Note that webinar medias and Soapbox videos imported to Wistia before September 1, 2023 cannot be archived.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

**Source/evidence:** [Archive Media](https://docs.wistia.com/reference/put_medias-archive.md), pinned SHA-256 `002cb1d95c81e84fc13d291d0c6af92d730eed060d709af924ce470424a1a016` in the inventory. Not marked experimental in the checked source. Synthetic offline contract: `tests/Contract/Data/ArchiveMediaTest.php` (shared cases/assertions in `MediaCases`/`MediaOperationContract`, plus `tests/Offline/MediaLifecycleTest.php`). No live or plan-entitlement verification.


## put_medias-move

**API:** `DataMediaService::moveMedia(Request\Data\MoveMediaRequest): Result\Data\MoveMediaResult`.

**Wire:** `PUT /medias/move`; Bearer token and version `2026-09` on the modern Data host. Path identifiers are raw strings encoded once.

| Input | Type | Location / requirement |
| --- | --- | --- |
| `hashed_ids` | `list<string>` | JSON body; required |
| `folder_id` | `string` | JSON body; required |
| `subfolder_id` | `string` | JSON body; optional |

**Success:** 200 JSON, 207 JSON. The named result exposes typed `data` and HTTP `status`; see its [PHP declaration](../../src/Result/Data/MoveMediaResult.php).

**Restrictions:** Moves up to 100 media to a folder and optional subfolder. The subfolder must
belong to the specified folder.

This endpoint allows 10 requests per 5 minutes, separate from the general
API rate limit. Returns a Background Job because the move is asynchronous.

For more than 100 media, multiple destinations, or mixed actions, use the
Create Bulk Actions endpoint with `move` actions.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and
authorizations granting the `update` permission on every media being moved
and on the destination folder can also be used. `subfolder_id` is not
available to expiring access tokens.

**Source/evidence:** [Move Media](https://docs.wistia.com/reference/put_medias-move.md), pinned SHA-256 `f2205685f8b17b0d60e2a0f756b88284ed7c4925183e1b698dbb53952c8632ee` in the inventory. Not marked experimental in the checked source. Synthetic offline contract: `tests/Contract/Data/MoveMediaTest.php` (shared cases/assertions in `MediaCases`/`MediaOperationContract`, plus `tests/Offline/MediaLifecycleTest.php`). No live or plan-entitlement verification.


## put_medias-restore

**API:** `DataMediaService::restoreMedia(Request\Data\RestoreMediaRequest): Result\Data\RestoreMediaResult`.

**Wire:** `PUT /medias/restore`; Bearer token and version `2026-09` on the modern Data host. Path identifiers are raw strings encoded once.

| Input | Type | Location / requirement |
| --- | --- | --- |
| `hashed_ids` | `list<string>` | JSON body; required |
| `folder_id` | `string` | JSON body; required |

**Success:** 200 JSON. The named result exposes typed `data` and HTTP `status`; see its [PHP declaration](../../src/Result/Data/RestoreMediaResult.php).

**Restrictions:** Restores archived medias to your account. This method accepts a list of up to 100 medias to restore per request. It processes requests asynchronously and will return a background_job_status object rather than the typical Media response object. Your account must have access to the Archiving feature to use this method.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

**Source/evidence:** [Restore Media](https://docs.wistia.com/reference/put_medias-restore.md), pinned SHA-256 `4265e8b534b911b95f97c98e9542fc355524481393059065897f1260343246ce` in the inventory. Not marked experimental in the checked source. Synthetic offline contract: `tests/Contract/Data/RestoreMediaTest.php` (shared cases/assertions in `MediaCases`/`MediaOperationContract`, plus `tests/Offline/MediaLifecycleTest.php`). No live or plan-entitlement verification.


## put_medias-copy

**API:** `DataMediaService::bulkCopyMedia(Request\Data\BulkCopyMediaRequest): Result\Data\BulkCopyMediaResult`.

**Wire:** `PUT /medias/copy`; Bearer token and version `2026-09` on the modern Data host. Path identifiers are raw strings encoded once.

| Input | Type | Location / requirement |
| --- | --- | --- |
| `hashed_ids` | `list<string>` | JSON body; required |
| `folder_id` | `string` | JSON body; required |

**Success:** 200 JSON. The named result exposes typed `data` and HTTP `status`; see its [PHP declaration](../../src/Result/Data/BulkCopyMediaResult.php).

**Restrictions:** This method accepts a list of medias to copy to a destination folder. It processes requests asynchronously and will return a background_job_status object rather than the typical Media response object.

Each media will be duplicated and the copy will be placed in the specified destination folder. The original media files will not be affected.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `update` permission on the destination folder
can also be used; only the media the token's authorizations name are
copied.

**Source/evidence:** [Bulk Copy Media](https://docs.wistia.com/reference/put_medias-copy.md), pinned SHA-256 `f75ade2c80ec6c7c0e34c3305a726aa7523774dbab30f73cd2c0b0477a752d0f` in the inventory. Not marked experimental in the checked source. Synthetic offline contract: `tests/Contract/Data/BulkCopyMediaTest.php` (shared cases/assertions in `MediaCases`/`MediaOperationContract`, plus `tests/Offline/MediaLifecycleTest.php`). No live or plan-entitlement verification.


## post_medias-mediahashedid-trims

**API:** `DataTrimsService::createMediaFromTrims(Request\Data\CreateMediaFromTrimsRequest): Result\Data\CreateMediaFromTrimsResult`.

**Wire:** `POST /medias/{mediaHashedId}/trims`; Bearer token and version `2026-09` on the modern Data host. Path identifiers are raw strings encoded once.

| Input | Type | Location / requirement |
| --- | --- | --- |
| `mediaHashedId` | `string` | path; required |
| `trims` | `list<string>` | JSON body; required |
| `keep_trims` | `boolean` | JSON body; optional |

**Success:** 200 JSON. The named result exposes typed `data` and HTTP `status`; see its [PHP declaration](../../src/Result/Data/CreateMediaFromTrimsResult.php).

**Restrictions:** Creates a new media that trims off parts of an existing media.

By default, the `trims` parameter specifies time ranges to **remove** from the media. When `keep_trims` is set to `true`, the `trims` parameter instead specifies time ranges to **keep** in the media.

**NOTE:** currently this endpoint only supports trimming video files.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `edit` permission on this media can also be used.

**Source/evidence:** [Create Media from Trims](https://docs.wistia.com/reference/post_medias-mediahashedid-trims.md), pinned SHA-256 `9e40ead2b9ee8002dbb69be2af277f74493df1db798ce333cc8b06898e5936d4` in the inventory. Not marked experimental in the checked source. Synthetic offline contract: `tests/Contract/Data/CreateMediaFromTrimsTest.php` (shared cases/assertions in `MediaCases`/`MediaOperationContract`, plus `tests/Offline/MediaLifecycleTest.php`). No live or plan-entitlement verification.


## get_tags

**API:** `DataTagsService::listTags(Request\Data\ListTagsRequest): Result\Data\ListTagsResult`.

**Wire:** `GET /tags`; Bearer token and version `2026-09` on the modern Data host. Path identifiers are raw strings encoded once.

| Input | Type | Location / requirement |
| --- | --- | --- |
| `page` | `integer` | query; optional |
| `per_page` | `integer` | query; optional |
| `cursor` | `Cursor` | query; optional |
| `sort_by` | `string` | query; optional |
| `sort_direction` | `integer` | query; optional |

**Success:** 200 JSON. The named result exposes typed `data` and HTTP `status`; see its [PHP declaration](../../src/Result/Data/ListTagsResult.php).

**Restrictions:** Lists tags belonging to the account.

## Requires api token with one of the following permissions
```
Read all data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `view-tags` or `manage-tags` permission on the
account can also be used.

**Source/evidence:** [List Tags](https://docs.wistia.com/reference/get_tags.md), pinned SHA-256 `a2e7540c270cf416ddf53c2da3e5ad4543f1d656a8882bc6a85b22750b4406d6` in the inventory. Not marked experimental in the checked source. Synthetic offline contract: `tests/Contract/Data/ListTagsTest.php` (shared cases/assertions in `MediaCases`/`MediaOperationContract`, plus `tests/Offline/MediaLifecycleTest.php`). No live or plan-entitlement verification.


## post_tags

**API:** `DataTagsService::createTags(Request\Data\CreateTagsRequest): Result\Data\CreateTagsResult`.

**Wire:** `POST /tags`; Bearer token and version `2026-09` on the modern Data host. Path identifiers are raw strings encoded once.

| Input | Type | Location / requirement |
| --- | --- | --- |
| `name` | `string` | JSON body; required |

**Success:** 200 JSON. The named result exposes typed `data` and HTTP `status`; see its [PHP declaration](../../src/Result/Data/CreateTagsResult.php).

**Restrictions:** Creates a new tag.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `manage-tags` permission on the account can also
be used.

**Source/evidence:** [Create Tags](https://docs.wistia.com/reference/post_tags.md), pinned SHA-256 `6dae799176e34473ad242728523b03a4a3a038f1f4a20f76173fa1c473cda89e` in the inventory. Not marked experimental in the checked source. Synthetic offline contract: `tests/Contract/Data/CreateTagsTest.php` (shared cases/assertions in `MediaCases`/`MediaOperationContract`, plus `tests/Offline/MediaLifecycleTest.php`). No live or plan-entitlement verification.


## delete_tags-name

**API:** `DataTagsService::deleteTag(Request\Data\DeleteTagRequest): Result\Data\DeleteTagResult`.

**Wire:** `DELETE /tags/{name}`; Bearer token and version `2026-09` on the modern Data host. Path identifiers are raw strings encoded once.

| Input | Type | Location / requirement |
| --- | --- | --- |
| `name` | `string` | path; required |

**Success:** 200 (no response schema documented). The named result exposes typed `data` and HTTP `status`; see its [PHP declaration](../../src/Result/Data/DeleteTagResult.php).

**Restrictions:** Deletes a tag

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `manage-tags` permission on the account can also
be used.

**Source/evidence:** [Delete Tag](https://docs.wistia.com/reference/delete_tags-name.md), pinned SHA-256 `acd6c345cc97ad9430a91a17113f3d679106149e1968ea2e721152ed3444665a` in the inventory. Not marked experimental in the checked source. Synthetic offline contract: `tests/Contract/Data/DeleteTagTest.php` (shared cases/assertions in `MediaCases`/`MediaOperationContract`, plus `tests/Offline/MediaLifecycleTest.php`). No live or plan-entitlement verification.


## post_taggings-bulk-create

**API:** `DataTaggingsService::bulkTagMedia(Request\Data\BulkTagMediaRequest): Result\Data\BulkTagMediaResult`.

**Wire:** `POST /taggings/bulk_create`; Bearer token and version `2026-09` on the modern Data host. Path identifiers are raw strings encoded once.

| Input | Type | Location / requirement |
| --- | --- | --- |
| `hashed_ids` | `list<string>` | JSON body; required |
| `tag_names` | `list<string>` | JSON body; required |

**Success:** 200 JSON. The named result exposes typed `data` and HTTP `status`; see its [PHP declaration](../../src/Result/Data/BulkTagMediaResult.php).

**Restrictions:** This method accepts a list of medias to tag. It processes requests asynchronously and will return a background_job_status object rather than the typical Media response object.

The tags will be added to the existing tags on each media file, not replaced.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

Expiring access tokens created with authorizations cannot be used; such
requests fail with a 403.

**Source/evidence:** [Bulk Tag Media](https://docs.wistia.com/reference/post_taggings-bulk-create.md), pinned SHA-256 `3713174195821d9537694fef71fe1d9e2ff7939ea658c7d11632d4e045ed8f9d` in the inventory. Not marked experimental in the checked source. Synthetic offline contract: `tests/Contract/Data/BulkTagMediaTest.php` (shared cases/assertions in `MediaCases`/`MediaOperationContract`, plus `tests/Offline/MediaLifecycleTest.php`). No live or plan-entitlement verification.


## get_deleted-media

**API:** `DataDeletedMediaService::listDeletedMedia(Request\Data\ListDeletedMediaRequest): Result\Data\ListDeletedMediaResult`.

**Wire:** `GET /deleted_media`; Bearer token and version `2026-09` on the modern Data host. Path identifiers are raw strings encoded once.

| Input | Type | Location / requirement |
| --- | --- | --- |
| `hashed_ids` | `list<string>` | query; optional; wire `hashed_ids[]` |
| `sort_by` | `string` | query; optional |
| `sort_direction` | `integer` | query; optional |
| `page` | `integer` | query; optional |
| `per_page` | `integer` | query; optional |
| `cursor` | `Cursor` | query; optional |

**Success:** 200 JSON. The named result exposes typed `data` and HTTP `status`; see its [PHP declaration](../../src/Result/Data/ListDeletedMediaResult.php).

**Restrictions:** Lists media that has been soft-deleted and is still inside the account's
restore window. Media is listed only while it can still be restored — 30 days
on most plans, 14 on free plans. After which it is permanently purged.


## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
`account` authorization granting the `manage-deleted-media` permission can
also be used. Only the deleted media the token's `media` authorizations
name are listed.

**Source/evidence:** [List Deleted Media](https://docs.wistia.com/reference/get_deleted-media.md), pinned SHA-256 `2d099a4a9a45a464c9bba6900f086da168ef9ecee5e9c0c5452834cad1ef368d` in the inventory. Not marked experimental in the checked source. Synthetic offline contract: `tests/Contract/Data/ListDeletedMediaTest.php` (shared cases/assertions in `MediaCases`/`MediaOperationContract`, plus `tests/Offline/MediaLifecycleTest.php`). No live or plan-entitlement verification.


## post_deleted-media-restore

**API:** `DataDeletedMediaService::restoreDeletedMedia(Request\Data\RestoreDeletedMediaRequest): Result\Data\RestoreDeletedMediaResult`.

**Wire:** `POST /deleted_media/restore`; Bearer token and version `2026-09` on the modern Data host. Path identifiers are raw strings encoded once.

| Input | Type | Location / requirement |
| --- | --- | --- |
| `media_hashed_ids` | `list<string>` | JSON body; required |
| `folder_id` | `string` | JSON body; optional |

**Success:** 202 JSON. The named result exposes typed `data` and HTTP `status`; see its [PHP declaration](../../src/Result/Data/RestoreDeletedMediaResult.php).

**Restrictions:** Restores one or more soft-deleted media. By default each media returns to the
folder it was deleted from; pass folder_id to restore them into a specific
folder instead. Only media still inside the restore window can be recovered.
The restore runs asynchronously and the response includes a background job
status.


## Requires api token with one of the following permissions
```
Upload and view media
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
`account` authorization granting the `manage-deleted-media` permission can
also be used. Only the media the token's `media` authorizations name are
restored, and `folder_id` also requires the `upload` permission on that
folder.

**Source/evidence:** [Restore Deleted Media](https://docs.wistia.com/reference/post_deleted-media-restore.md), pinned SHA-256 `50703aa8674f48761e191d7009e3dfadcc73e69fe61d82e504cc04fdcbc89ccc` in the inventory. Not marked experimental in the checked source. Synthetic offline contract: `tests/Contract/Data/RestoreDeletedMediaTest.php` (shared cases/assertions in `MediaCases`/`MediaOperationContract`, plus `tests/Offline/MediaLifecycleTest.php`). No live or plan-entitlement verification.

## Customization operation conventions

The [usage guide](../customizations.md#nested-input-and-results) documents sharing-list replacement/download coupling, narrative-derived option validation, gradient pairs, and the maintainer-approved image-CTA `altText` interpretation. The inventory preserves the upstream narrative/closed-schema discrepancy; this is not live-verified behavior.

P07 implements the following 24 operations. See [media customizations](../customizations.md) for runnable usage, replacement versus partial-update/null semantics, nested input/result records, provider rules and migration. Every method accepts its named request with a nonempty raw `mediaId` encoded once as a path segment. All use Bearer authentication and the `2026-09` version, no query or pagination. Bodies use JSON and exact wire names; results expose `status` and typed `data`, except schema-less deletion which exposes only `status`. Shared sanitized failures, rate-limit information and single-attempt uncertain-write handling apply. These methods are not marked experimental by the pinned sources; all live and plan/provider behavior remains unverified.

## get_medias-mediaid-customizations

**API:** `DataCustomizationsService::showCustomizations(Request\Data\ShowCustomizationsRequest): Result\Data\ShowCustomizationsResult`.

**Wire:** `GET /medias/{mediaId}/customizations`; success 200. No request body. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Fetches explicitly defined customizations for the video.

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization for this media can also be used; any permission granted on
the media allows reading its customizations.

**Source:** [Show Customizations](https://docs.wistia.com/reference/get_medias-mediaid-customizations.md), pinned SHA-256 `5ac9886985d90b8302cdca991c8acbb6b01f129d900b3ae1e158d679ebe6dc19`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/ShowCustomizationsTest.php`; synthetic coverage is not live verification.

## post_medias-mediaid-customizations

**API:** `DataCustomizationsService::createCustomizations(Request\Data\CreateCustomizationsRequest): Result\Data\CreateCustomizationsResult`.

**Wire:** `POST /medias/{mediaId}/customizations`; success 201. Supply a `Customizations\CreateCustomizationsInput` as the request's `customizations` argument. Its public nested records preserve the source schema fields. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Set customizations for a video. Replaces the customizations explicitly set for this video.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `edit` permission on this media can also be used.

**Source:** [Create Customizations](https://docs.wistia.com/reference/post_medias-mediaid-customizations.md), pinned SHA-256 `2f93abdd2324824d52eea672b40b5e1a00a9a32f21ab5de22ab28b1464b3c842`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/CreateCustomizationsTest.php`; synthetic coverage is not live verification.

## put_medias-mediaid-customizations

**API:** `DataCustomizationsService::updateCustomizations(Request\Data\UpdateCustomizationsRequest): Result\Data\UpdateCustomizationsResult`.

**Wire:** `PUT /medias/{mediaId}/customizations`; success 200. Supply a `Customizations\UpdateCustomizationsInput` as the request's `customizations` argument. Its public nested records preserve the source schema fields. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Allows for partial updates on a video’s customizations. If a value is null, then that key will be deleted from the saved customizations. If it is not null, that value will be set.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `edit` permission on this media can also be used.

**Source:** [Update Customizations](https://docs.wistia.com/reference/put_medias-mediaid-customizations.md), pinned SHA-256 `6e343bb30c39f609b3cbe9c87091869104581cc020cac872b864d0f7da7ef252`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/UpdateCustomizationsTest.php`; synthetic coverage is not live verification.

## delete_medias-mediaid-customizations

**API:** `DataCustomizationsService::deleteCustomizations(Request\Data\DeleteCustomizationsRequest): Result\Data\DeleteCustomizationsResult`.

**Wire:** `DELETE /medias/{mediaId}/customizations`; success 200. No request body. No response schema is documented; response bytes are not interpreted as JSON.

**Restrictions:**

Deletes all explicit customizations for a video, making it act as if it has never been customized.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `edit` permission on this media can also be used.

**Source:** [Delete Customizations](https://docs.wistia.com/reference/delete_medias-mediaid-customizations.md), pinned SHA-256 `80d5dfe76d687e82c0a7f0d515d7394a948433f2b3b4004309c2fe34607242fe`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/DeleteCustomizationsTest.php`; synthetic coverage is not live verification.

## get_medias-mediaid-customizations-appearance

**API:** `DataCustomizationsService::showAppearanceCustomizations(Request\Data\ShowAppearanceCustomizationsRequest): Result\Data\ShowAppearanceCustomizationsResult`.

**Wire:** `GET /medias/{mediaId}/customizations/appearance`; success 200. No request body. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Fetches the explicitly-set appearance customizations (player color, gradient,
rounded corners, control contrast, and customer logo) for the video.

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization for this media can also be used; any permission granted on
the media allows reading its customizations.

**Source:** [Show Appearance Customizations](https://docs.wistia.com/reference/get_medias-mediaid-customizations-appearance.md), pinned SHA-256 `0b48aedbfc0b6ca6aa947dbe5dc3c80bf7b92ad00445b5dc14061e8ddb11a52e`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/ShowAppearanceCustomizationsTest.php`; synthetic coverage is not live verification.

## put_medias-mediaid-customizations-appearance

**API:** `DataCustomizationsService::updateAppearanceCustomizations(Request\Data\UpdateAppearanceCustomizationsRequest): Result\Data\UpdateAppearanceCustomizationsResult`.

**Wire:** `PUT /medias/{mediaId}/customizations/appearance`; success 200. Supply a `Customizations\UpdateAppearanceCustomizationsInput` as the request's `customizations` argument. Its public nested records preserve the source schema fields. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Applies a partial update to a video's appearance customizations. Only the
fields supplied are changed; sending a field as null deletes it (reverting to
the default).

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `edit` permission on this media can also be used.

**Source:** [Update Appearance Customizations](https://docs.wistia.com/reference/put_medias-mediaid-customizations-appearance.md), pinned SHA-256 `19f07479f3aaab07a5bb04b2dd0b8dbe2e1393e70d1bb644632716f1e86e8e97`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/UpdateAppearanceCustomizationsTest.php`; synthetic coverage is not live verification.

## get_medias-mediaid-customizations-playback

**API:** `DataCustomizationsService::showPlaybackCustomizations(Request\Data\ShowPlaybackCustomizationsRequest): Result\Data\ShowPlaybackCustomizationsResult`.

**Wire:** `GET /medias/{mediaId}/customizations/playback`; success 200. No request body. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Fetches the explicitly-set playback customizations (autoplay, mute, controls
visibility, player control buttons, end behavior, looping, quality bounds, and
embed/runtime flags) for the video.

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization for this media can also be used; any permission granted on
the media allows reading its customizations.

**Source:** [Show Playback Customizations](https://docs.wistia.com/reference/get_medias-mediaid-customizations-playback.md), pinned SHA-256 `f86fd10aca174560b9ad442674e3aafdf6e3d1f610eba339d1ba2360cddae370`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/ShowPlaybackCustomizationsTest.php`; synthetic coverage is not live verification.

## put_medias-mediaid-customizations-playback

**API:** `DataCustomizationsService::updatePlaybackCustomizations(Request\Data\UpdatePlaybackCustomizationsRequest): Result\Data\UpdatePlaybackCustomizationsResult`.

**Wire:** `PUT /medias/{mediaId}/customizations/playback`; success 200. Supply a `Customizations\UpdatePlaybackCustomizationsInput` as the request's `customizations` argument. Its public nested records preserve the source schema fields. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Applies a partial update to a video's playback customizations. Only the
fields supplied are changed; sending a field as null deletes it (reverting to
the default).

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `edit` permission on this media can also be used.

**Source:** [Update Playback Customizations](https://docs.wistia.com/reference/put_medias-mediaid-customizations-playback.md), pinned SHA-256 `cc8aa2a8c689eebd4203259d968fbd9dab3d7fe7d970a0f7f9b6ea090c31e37a`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/UpdatePlaybackCustomizationsTest.php`; synthetic coverage is not live verification.

## get_medias-mediaid-customizations-thumbnail

**API:** `DataCustomizationsService::showThumbnailCustomizations(Request\Data\ShowThumbnailCustomizationsRequest): Result\Data\ShowThumbnailCustomizationsResult`.

**Wire:** `GET /medias/{mediaId}/customizations/thumbnail`; success 200. No request body. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Fetches the explicitly-set thumbnail customizations (still image URL, alt
text, fit strategy, and the looping video thumbnail / text-overlay plugins)
for the video.

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization for this media can also be used; any permission granted on
the media allows reading its customizations.

**Source:** [Show Thumbnail Customizations](https://docs.wistia.com/reference/get_medias-mediaid-customizations-thumbnail.md), pinned SHA-256 `652664ccc58eed2ed0b61c2f1c03cb6da108801b71afd5ff05ae78a6e2a28405`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/ShowThumbnailCustomizationsTest.php`; synthetic coverage is not live verification.

## put_medias-mediaid-customizations-thumbnail

**API:** `DataCustomizationsService::updateThumbnailCustomizations(Request\Data\UpdateThumbnailCustomizationsRequest): Result\Data\UpdateThumbnailCustomizationsResult`.

**Wire:** `PUT /medias/{mediaId}/customizations/thumbnail`; success 200. Supply a `Customizations\UpdateThumbnailCustomizationsInput` as the request's `customizations` argument. Its public nested records preserve the source schema fields. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Applies a partial update to a video's thumbnail customizations. Only the
fields supplied are changed; sending a field as null deletes it (reverting to
the default).

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `edit` permission on this media can also be used.

**Source:** [Update Thumbnail Customizations](https://docs.wistia.com/reference/put_medias-mediaid-customizations-thumbnail.md), pinned SHA-256 `2dd1a2ab081243d5f778e5b66b15014d34858a6c3ccf2178ee4609b9aae3637c`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/UpdateThumbnailCustomizationsTest.php`; synthetic coverage is not live verification.

## get_medias-mediaid-customizations-accessibility

**API:** `DataCustomizationsService::showAccessibilityCustomizations(Request\Data\ShowAccessibilityCustomizationsRequest): Result\Data\ShowAccessibilityCustomizationsResult`.

**Wire:** `GET /medias/{mediaId}/customizations/accessibility`; success 200. No request body. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Fetches the explicitly-set accessibility customizations (caption display and
styling, transcript display, and audio description) for the video.

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization for this media can also be used; any permission granted on
the media allows reading its customizations.

**Source:** [Show Accessibility Customizations](https://docs.wistia.com/reference/get_medias-mediaid-customizations-accessibility.md), pinned SHA-256 `46ac246b9c66f2c3bb14dd26e8487832201a0a9117d743a3ba09efd02f9dea39`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/ShowAccessibilityCustomizationsTest.php`; synthetic coverage is not live verification.

## put_medias-mediaid-customizations-accessibility

**API:** `DataCustomizationsService::updateAccessibilityCustomizations(Request\Data\UpdateAccessibilityCustomizationsRequest): Result\Data\UpdateAccessibilityCustomizationsResult`.

**Wire:** `PUT /medias/{mediaId}/customizations/accessibility`; success 200. Supply a `Customizations\UpdateAccessibilityCustomizationsInput` as the request's `customizations` argument. Its public nested records preserve the source schema fields. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Applies a partial update to a video's accessibility customizations. Only the
fields supplied are changed; sending a field as null deletes it (reverting to
the default).

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `edit` permission on this media can also be used.

**Source:** [Update Accessibility Customizations](https://docs.wistia.com/reference/put_medias-mediaid-customizations-accessibility.md), pinned SHA-256 `af68b5b1d503daa50f8b4d6ede14765e3d5aae864290e181f7e8d8701b2ec9f7`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/UpdateAccessibilityCustomizationsTest.php`; synthetic coverage is not live verification.

## get_medias-mediaid-customizations-chapters

**API:** `DataCustomizationsService::showChaptersCustomizations(Request\Data\ShowChaptersCustomizationsRequest): Result\Data\ShowChaptersCustomizationsResult`.

**Wire:** `GET /medias/{mediaId}/customizations/chapters`; success 200. No request body. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Fetches the explicitly-set chapter customizations (the chapter list and its
visibility) for the media.

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization for this media can also be used; any permission granted on
the media allows reading its customizations.

**Source:** [Show Chapters Customizations](https://docs.wistia.com/reference/get_medias-mediaid-customizations-chapters.md), pinned SHA-256 `27c726d86705209e5971cebb1ec953becfd183590a7fe46b9634e2566035ce63`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/ShowChaptersCustomizationsTest.php`; synthetic coverage is not live verification.

## put_medias-mediaid-customizations-chapters

**API:** `DataCustomizationsService::updateChaptersCustomizations(Request\Data\UpdateChaptersCustomizationsRequest): Result\Data\UpdateChaptersCustomizationsResult`.

**Wire:** `PUT /medias/{mediaId}/customizations/chapters`; success 200. Supply a `Customizations\UpdateChaptersCustomizationsInput` as the request's `customizations` argument. Its public nested records preserve the source schema fields. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Applies a partial update to a media's chapter customizations. Only the fields
supplied are changed; sending a field as null deletes it.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `edit` permission on this media can also be used.

**Source:** [Update Chapters Customizations](https://docs.wistia.com/reference/put_medias-mediaid-customizations-chapters.md), pinned SHA-256 `5456d573c7497e8783cf064e18ac57773f09e8884583fd2d81af1342b8733c57`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/UpdateChaptersCustomizationsTest.php`; synthetic coverage is not live verification.

## get_medias-mediaid-customizations-engagement

**API:** `DataCustomizationsService::showEngagementCustomizations(Request\Data\ShowEngagementCustomizationsRequest): Result\Data\ShowEngagementCustomizationsResult`.

**Wire:** `GET /medias/{mediaId}/customizations/engagement`; success 200. No request body. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Fetches the explicitly-set engagement customizations (the end/pause Call To
Action and timed annotation links) for the video.

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization for this media can also be used; any permission granted on
the media allows reading its customizations.

**Source:** [Show Engagement Customizations](https://docs.wistia.com/reference/get_medias-mediaid-customizations-engagement.md), pinned SHA-256 `0926a2569f26d7faff2cbaa6a37ec5a7e516758cbf98b540c416eda130317197`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/ShowEngagementCustomizationsTest.php`; synthetic coverage is not live verification.

## put_medias-mediaid-customizations-engagement

**API:** `DataCustomizationsService::updateEngagementCustomizations(Request\Data\UpdateEngagementCustomizationsRequest): Result\Data\UpdateEngagementCustomizationsResult`.

**Wire:** `PUT /medias/{mediaId}/customizations/engagement`; success 200. Supply a `Customizations\UpdateEngagementCustomizationsInput` as the request's `customizations` argument. Its public nested records preserve the source schema fields. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Applies a partial update to a video's engagement customizations. Only the
fields supplied are changed; sending a field as null deletes it (reverting to
the default).

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `edit` permission on this media can also be used.

**Source:** [Update Engagement Customizations](https://docs.wistia.com/reference/put_medias-mediaid-customizations-engagement.md), pinned SHA-256 `69e7913e50c81f9d47ea4b5ffeff3cf99f4c06b6661ef53d29eb41ceb4774afa`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/UpdateEngagementCustomizationsTest.php`; synthetic coverage is not live verification.

## get_medias-mediaid-customizations-related-media

**API:** `DataCustomizationsService::showRelatedMediaCustomizations(Request\Data\ShowRelatedMediaCustomizationsRequest): Result\Data\ShowRelatedMediaCustomizationsResult`.

**Wire:** `GET /medias/{mediaId}/customizations/related_media`; success 200. No request body. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Fetches the explicitly-set related-media ("discover more") customizations
(which videos to recommend, when to show them, label and button text) for
the video.

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization for this media can also be used; any permission granted on
the media allows reading its customizations.

**Source:** [Show Related Media Customizations](https://docs.wistia.com/reference/get_medias-mediaid-customizations-related-media.md), pinned SHA-256 `5aec6c354c7680d2d1668f624ecf95db0d6078c12435b6b7bdccf7c1d1f50e8d`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/ShowRelatedMediaCustomizationsTest.php`; synthetic coverage is not live verification.

## put_medias-mediaid-customizations-related-media

**API:** `DataCustomizationsService::updateRelatedMediaCustomizations(Request\Data\UpdateRelatedMediaCustomizationsRequest): Result\Data\UpdateRelatedMediaCustomizationsResult`.

**Wire:** `PUT /medias/{mediaId}/customizations/related_media`; success 200. Supply a `Customizations\UpdateRelatedMediaCustomizationsInput` as the request's `customizations` argument. Its public nested records preserve the source schema fields. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Applies a partial update to a video's related-media customizations. Only the
fields supplied are changed; sending a field as null deletes it (reverting to
the default).

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `edit` permission on this media can also be used.

**Source:** [Update Related Media Customizations](https://docs.wistia.com/reference/put_medias-mediaid-customizations-related-media.md), pinned SHA-256 `243e7fadee35b6ac1b329de8b08549c9a42998901ab66bb0a12579a8d2367cba`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/UpdateRelatedMediaCustomizationsTest.php`; synthetic coverage is not live verification.

## get_medias-mediaid-customizations-sharing

**API:** `DataCustomizationsService::showSharingCustomizations(Request\Data\ShowSharingCustomizationsRequest): Result\Data\ShowSharingCustomizationsResult`.

**Wire:** `GET /medias/{mediaId}/customizations/sharing`; success 200. No request body. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Fetches the explicitly-set sharing customizations (the social/embed/download
share bar: enabled channels, tweet text, download type, and page URL/title)
for the video.

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization for this media can also be used; any permission granted on
the media allows reading its customizations.

**Source:** [Show Sharing Customizations](https://docs.wistia.com/reference/get_medias-mediaid-customizations-sharing.md), pinned SHA-256 `a63d1fd4d89ded697ec72df10c75956fffec4bb88bf1e1c5ff51857de6ff69e7`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/ShowSharingCustomizationsTest.php`; synthetic coverage is not live verification.

## put_medias-mediaid-customizations-sharing

**API:** `DataCustomizationsService::updateSharingCustomizations(Request\Data\UpdateSharingCustomizationsRequest): Result\Data\UpdateSharingCustomizationsResult`.

**Wire:** `PUT /medias/{mediaId}/customizations/sharing`; success 200. Supply a `Customizations\UpdateSharingCustomizationsInput` as the request's `customizations` argument. Its public nested records preserve the source schema fields. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Applies a partial update to a video's sharing customizations. Only the fields
supplied are changed; sending a field as null deletes it (reverting to the
default).

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `edit` permission on this media can also be used.

**Source:** [Update Sharing Customizations](https://docs.wistia.com/reference/put_medias-mediaid-customizations-sharing.md), pinned SHA-256 `4f8e7102811e98869e3c8db1aae45543238444080e58dc9f86658fd19d60520a`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/UpdateSharingCustomizationsTest.php`; synthetic coverage is not live verification.

## get_medias-mediaid-customizations-lead-capture

**API:** `DataCustomizationsService::showLeadCaptureCustomizations(Request\Data\ShowLeadCaptureCustomizationsRequest): Result\Data\ShowLeadCaptureCustomizationsResult`.

**Wire:** `GET /medias/{mediaId}/customizations/lead_capture`; success 200. No request body. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Fetches the explicitly-set lead-capture plugins (Turnstile, Wistia Form, and
HubSpot/Marketo/Pardot form embeds) for the video.

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization for this media can also be used; any permission granted on
the media allows reading its customizations.

**Source:** [Show Lead Capture Customizations](https://docs.wistia.com/reference/get_medias-mediaid-customizations-lead-capture.md), pinned SHA-256 `6db77049661c9825e72dd7c9ef0771449ce82948dcc3309094bf339514154733`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/ShowLeadCaptureCustomizationsTest.php`; synthetic coverage is not live verification.

## put_medias-mediaid-customizations-lead-capture

**API:** `DataCustomizationsService::updateLeadCaptureCustomizations(Request\Data\UpdateLeadCaptureCustomizationsRequest): Result\Data\UpdateLeadCaptureCustomizationsResult`.

**Wire:** `PUT /medias/{mediaId}/customizations/lead_capture`; success 200. Supply a `Customizations\UpdateLeadCaptureCustomizationsInput` as the request's `customizations` argument. Its public nested records preserve the source schema fields. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Configures a single lead-capture provider for the video, mapping it to the
appropriate underlying plugin. Only the selected provider is changed.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `edit` permission on this media can also be used.

**Source:** [Update Lead Capture Customizations](https://docs.wistia.com/reference/put_medias-mediaid-customizations-lead-capture.md), pinned SHA-256 `aae7382b4f0f60eff2d714510da037aef1a68f901fd4a9d0726cc8b148960906`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/UpdateLeadCaptureCustomizationsTest.php`; synthetic coverage is not live verification.

## get_medias-mediaid-customizations-access

**API:** `DataCustomizationsService::showAccessCustomizations(Request\Data\ShowAccessCustomizationsRequest): Result\Data\ShowAccessCustomizationsResult`.

**Wire:** `GET /medias/{mediaId}/customizations/access`; success 200. No request body. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Fetches the explicitly-set password-protection settings for the video,
including the stored password.

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization for this media can also be used; any permission granted on
the media allows reading its customizations.

**Source:** [Show Access Customizations](https://docs.wistia.com/reference/get_medias-mediaid-customizations-access.md), pinned SHA-256 `c2b308ea31b86db2feb1533b483d277a7d2472453c67a296fb57f5f1297d34aa`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/ShowAccessCustomizationsTest.php`; synthetic coverage is not live verification.

## put_medias-mediaid-customizations-access

**API:** `DataCustomizationsService::updateAccessCustomizations(Request\Data\UpdateAccessCustomizationsRequest): Result\Data\UpdateAccessCustomizationsResult`.

**Wire:** `PUT /medias/{mediaId}/customizations/access`; success 200. Supply a `Customizations\UpdateAccessCustomizationsInput` as the request's `customizations` argument. Its public nested records preserve the source schema fields. Successful JSON is decoded into typed nested customization records. String-valued flags/numbers remain strings; omitted fields are tracked with `presentFields`.

**Restrictions:**

Applies a partial update to a video's password-protection settings. Only the
fields supplied are changed; sending a field as null deletes it.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `edit` permission on this media can also be used.

**Source:** [Update Access Customizations](https://docs.wistia.com/reference/put_medias-mediaid-customizations-access.md), pinned SHA-256 `6bd41c4cea29866c786a3f93e68f46bd57a4315a2061439e07268036ba0b9168`. See the [inventory](../rewrite/operations.json) for the retained field schemas and documented error statuses. Offline contract target: `tests/Contract/Data/UpdateAccessCustomizationsTest.php`; synthetic coverage is not live verification.

## Captions and accessibility

P08 adds 16 operations. See the [usage guide](../accessibility.md) for caption formats, exact versus fuzzy matches, atomic versioned edits, pagination, existing-order status, permissions and migration. Each method accepts its named request and returns its named result with `status` and typed `data`. Caption create/replace/delete have no source-defined success schema; their results retain status with `data=null`, without asserting a response-body shape. All use Bearer authentication, version `2026-09`, single-attempt shared execution and sanitized errors. None is marked experimental by the pinned sources. Live behavior and plan entitlement remain unverified.

## get_medias-mediahashedid-captions

**API:** `DataCaptionsService::listCaptionsByMedia(Request\Data\ListCaptionsByMediaRequest): Result\Data\ListCaptionsByMediaResult`.

**Wire:** `GET /medias/{mediaHashedId}/captions`; success 200. Path/query inputs: `mediaHashedId` (path).

**Restrictions:**

Lists captions belonging to a specific media.

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `edit-transcripts` permission on this media can
also be used. A token granted any other permission on the media lists no
captions.

**Source:** [List Captions by Media](https://docs.wistia.com/reference/get_medias-mediahashedid-captions.md), pinned SHA-256 `f098aabd98d40f44d628b27ccbbaa150c378990de7d38dc1818d480d210eb20e`. See the [inventory](../rewrite/operations.json) for exact schemas, limits and error statuses. Offline contract: `tests/Contract/Data/ListCaptionsByMediaTest.php`; behavioral edge cases: `tests/Contract/Data/AccessibilityBehaviorTest.php`.

## post_medias-mediahashedid-captions

**API:** `DataCaptionsService::createCaptions(Request\Data\CreateCaptionsRequest): Result\Data\CreateCaptionsResult`.

**Wire:** `POST /medias/{mediaHashedId}/captions`; success 200. JSON body with exact fields: `caption_file`, `language`. Path/query inputs: `mediaHashedId` (path).

**Restrictions:**

Adds captions to a specified media by providing an SRT file or its contents directly.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `create-transcripts` permission on this media can
also be used.

**Source:** [Create Captions](https://docs.wistia.com/reference/post_medias-mediahashedid-captions.md), pinned SHA-256 `5beedb2026fadf0dafabe6a04e2bcd95de100f9d11c1537150a5bdb69cd1dbc0`. See the [inventory](../rewrite/operations.json) for exact schemas, limits and error statuses. Offline contract: `tests/Contract/Data/CreateCaptionsTest.php`; behavioral edge cases: `tests/Contract/Data/AccessibilityBehaviorTest.php`.

## get_captions

**API:** `DataCaptionsService::listCaptions(Request\Data\ListCaptionsRequest): Result\Data\ListCaptionsResult`.

**Wire:** `GET /captions`; success 200. Path/query inputs: `media_id` (query), `media_ids[]` (query), `languages[]` (query), `include` (query), `page` (query), `per_page` (query), `cursor` (query), `sort_by` (query), `sort_direction` (query).

**Restrictions:**

Lists captions belonging to the account. Results can be narrowed to a specific media
with `media_id`, or to several media and languages at once with `media_ids[]` and
`languages[]`. Each caption includes its text, so combining these filters with
pagination fetches transcripts for many media in a few requests. Pass
`include=metadata` to omit transcript text when only track and language
information is needed.

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope can also be
used. It lists only the captions of media it grants the `edit-transcripts`
permission on; no other permission reaches a media's captions.

**Source:** [List Captions](https://docs.wistia.com/reference/get_captions.md), pinned SHA-256 `f6f61303c034db00ce0b58a39a6351836ab37cd0209b2026ddc4f703637932b0`. See the [inventory](../rewrite/operations.json) for exact schemas, limits and error statuses. Offline contract: `tests/Contract/Data/ListCaptionsTest.php`; behavioral edge cases: `tests/Contract/Data/AccessibilityBehaviorTest.php`.

## post_caption-matches

**API:** `DataCaptionsService::findCaptionMatches(Request\Data\FindCaptionMatchesRequest): Result\Data\FindCaptionMatchesResult`.

**Wire:** `POST /caption_matches`; success 200. JSON body with exact fields: `media_ids`, `target_text`, `language_code`, `occurrence`, `start_ms`, `end_ms`.

**Restrictions:**

Finds exact text in caption tracks without modifying them. Matching uses the
same normalization, composite-media boundaries, and time coordinates as the
targeted caption edit endpoint. Fuzzy alternatives are returned separately
as suggestions and are never reported as exact matches. A resolved match
means the wording was located; a later write can still fail authorization,
version, or edit-boundary checks.

When more than 10 exact matches exist, use the one-based `occurrence`
parameter to retrieve a specific later match.

Authentication and request validation failures apply to the whole request.
Missing, inaccessible, or otherwise unreadable media are reported as
per-media statuses without exposing whether an inaccessible ID exists.

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used.

**Source:** [Find Caption Matches](https://docs.wistia.com/reference/post_caption-matches.md), pinned SHA-256 `db40a8cccbf3c083ee03ad8da5c10a95f738f8bea8945588a5d7952ca60db7b0`. See the [inventory](../rewrite/operations.json) for exact schemas, limits and error statuses. Offline contract: `tests/Contract/Data/FindCaptionMatchesTest.php`; behavioral edge cases: `tests/Contract/Data/AccessibilityBehaviorTest.php`.

## get_medias-mediahashedid-captions-languagecode

**API:** `DataCaptionsService::showCaptions(Request\Data\ShowCaptionsRequest): Result\Data\ShowCaptionsResult`.

**Wire:** `GET /medias/{mediaHashedId}/captions/{languageCode}`; success 200. Path/query inputs: `mediaHashedId` (path), `languageCode` (path), `include` (query), `include_speakers` (query).

**Restrictions:**

Returns a media's captions in the specified language.
Supports multiple formats: JSON (default), SRT, VTT, and TXT.
Use file extensions (.srt, .vtt, .txt) or Accept headers to specify format.

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `edit-transcripts` permission on this media can
also be used. For a token granted any other permission on the media the
captions are not found.

**Source:** [Show Captions](https://docs.wistia.com/reference/get_medias-mediahashedid-captions-languagecode.md), pinned SHA-256 `338a744f538b93b99b897141a65328dd571710dce831fb40dab0d695837c3283`. See the [inventory](../rewrite/operations.json) for exact schemas, limits and error statuses. Offline contract: `tests/Contract/Data/ShowCaptionsTest.php`; behavioral edge cases: `tests/Contract/Data/AccessibilityBehaviorTest.php`.

## put_medias-mediahashedid-captions-languagecode

**API:** `DataCaptionsService::updateCaptions(Request\Data\UpdateCaptionsRequest): Result\Data\UpdateCaptionsResult`.

**Wire:** `PUT /medias/{mediaHashedId}/captions/{languageCode}`; success 200. JSON body with exact fields: `caption_file`. Path/query inputs: `mediaHashedId` (path), `languageCode` (path).

**Restrictions:**

This method is for replacing the captions on a video or audio media for the specified language.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `edit-transcripts` permission on this media can
also be used.

**Source:** [Update Captions](https://docs.wistia.com/reference/put_medias-mediahashedid-captions-languagecode.md), pinned SHA-256 `fb90da73b462a0c98a9f76bad857ec844a263eb8826e4ae68f59bb0784557b00`. See the [inventory](../rewrite/operations.json) for exact schemas, limits and error statuses. Offline contract: `tests/Contract/Data/UpdateCaptionsTest.php`; behavioral edge cases: `tests/Contract/Data/AccessibilityBehaviorTest.php`.

## delete_medias-mediahashedid-captions-languagecode

**API:** `DataCaptionsService::deleteCaptions(Request\Data\DeleteCaptionsRequest): Result\Data\DeleteCaptionsResult`.

**Wire:** `DELETE /medias/{mediaHashedId}/captions/{languageCode}`; success 200. Path/query inputs: `mediaHashedId` (path), `languageCode` (path).

**Restrictions:**

Removes the captions file from a media for the specified language.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `edit-transcripts` permission on this media can
also be used.

**Source:** [Delete Captions](https://docs.wistia.com/reference/delete_medias-mediahashedid-captions-languagecode.md), pinned SHA-256 `e5c5b61f2012eef1b8b0fd0eee1d46f0b0234870a7c845403c53ae32d4e194ba`. See the [inventory](../rewrite/operations.json) for exact schemas, limits and error statuses. Offline contract: `tests/Contract/Data/DeleteCaptionsTest.php`; behavioral edge cases: `tests/Contract/Data/AccessibilityBehaviorTest.php`.

## post_medias-mediahashedid-captions-languagecode-edits

**API:** `DataCaptionsService::editCaptionsText(Request\Data\EditCaptionsTextRequest): Result\Data\EditCaptionsTextResult`.

**Wire:** `POST /medias/{mediaHashedId}/captions/{languageCode}/edits`; success 200. JSON body with exact fields: `edits`, `expected_version`. Path/query inputs: `mediaHashedId` (path), `languageCode` (path).

**Restrictions:**

Applies targeted find-and-replace corrections to a media's transcript for
the specified language, preserving the timings of unchanged words. The whole
batch is applied atomically against a specific caption version, or nothing is.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `edit-transcripts` permission on this media can
also be used. When the media is a composite whose captions belong to
another media, the token also needs `edit-transcripts` on that media.

**Source:** [Edit Captions Text](https://docs.wistia.com/reference/post_medias-mediahashedid-captions-languagecode-edits.md), pinned SHA-256 `cbdf4763808af86e21f047908b402379030c157ec263608d8304a66882009fc7`. See the [inventory](../rewrite/operations.json) for exact schemas, limits and error statuses. Offline contract: `tests/Contract/Data/EditCaptionsTextTest.php`; behavioral edge cases: `tests/Contract/Data/AccessibilityBehaviorTest.php`.

## get_speakers

**API:** `DataSpeakersService::listSpeakers(Request\Data\ListSpeakersRequest): Result\Data\ListSpeakersResult`.

**Wire:** `GET /speakers`; success 200. Path/query inputs: `name` (query), `sort_by` (query), `sort_direction` (query), `page` (query), `per_page` (query), `cursor` (query).

**Restrictions:**

Lists reusable speaker profiles belonging to the account.


## Requires api token with one of the following permissions
```
Read all data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token. View-only contacts cannot list speaker
profiles.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
`account` authorization granting the `view-speakers` permission can also
be used.

**Source:** [List Speakers](https://docs.wistia.com/reference/get_speakers.md), pinned SHA-256 `bdc29d132bf54811bf3d02c20050eb34b17840800842d44ed4ae8a099d0eb839`. See the [inventory](../rewrite/operations.json) for exact schemas, limits and error statuses. Offline contract: `tests/Contract/Data/ListSpeakersTest.php`; behavioral edge cases: `tests/Contract/Data/AccessibilityBehaviorTest.php`.

## get_medias-mediahashedid-localizations

**API:** `DataLocalizationsService::listLocalizations(Request\Data\ListLocalizationsRequest): Result\Data\ListLocalizationsResult`.

**Wire:** `GET /medias/{mediaHashedId}/localizations`; success 200. Path/query inputs: `mediaHashedId` (path), `include_transcript` (query).

**Restrictions:**

Lists all the localizations for a media.

## Requires api token with one of the following permissions
```
Read all data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `edit-transcripts` permission on the source
media can also be used. Other media permissions do not reach a media's
localizations, so a token without `edit-transcripts` lists none.

**Source:** [List Localizations](https://docs.wistia.com/reference/get_medias-mediahashedid-localizations.md), pinned SHA-256 `6848e5c11179c63bb64d3d41828026d1cefdeb2b0faa833fb3b97772d3505676`. See the [inventory](../rewrite/operations.json) for exact schemas, limits and error statuses. Offline contract: `tests/Contract/Data/ListLocalizationsTest.php`; behavioral edge cases: `tests/Contract/Data/AccessibilityBehaviorTest.php`.

## get_medias-mediahashedid-localizations-localizationhashedid

**API:** `DataLocalizationsService::showLocalization(Request\Data\ShowLocalizationRequest): Result\Data\ShowLocalizationResult`.

**Wire:** `GET /medias/{mediaHashedId}/localizations/{localizationHashedId}`; success 200. Path/query inputs: `mediaHashedId` (path), `localizationHashedId` (path), `include_transcript` (query).

**Restrictions:**

Obtain detailed information about a localization.

## Requires api token with one of the following permissions
```
Read all data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `edit-transcripts` permission on the source
media can also be used. Other media permissions do not reach a media's
localizations, so a token without `edit-transcripts` gets a 404.

**Source:** [Show Localization](https://docs.wistia.com/reference/get_medias-mediahashedid-localizations-localizationhashedid.md), pinned SHA-256 `b8a26d2bf07c1964722cb637ed7cc8d50f93f680d8b7bb49772109a7d0013d6d`. See the [inventory](../rewrite/operations.json) for exact schemas, limits and error statuses. Offline contract: `tests/Contract/Data/ShowLocalizationTest.php`; behavioral edge cases: `tests/Contract/Data/AccessibilityBehaviorTest.php`.

## delete_medias-mediahashedid-localizations-localizationhashedid

**API:** `DataLocalizationsService::deleteLocalization(Request\Data\DeleteLocalizationRequest): Result\Data\DeleteLocalizationResult`.

**Wire:** `DELETE /medias/{mediaHashedId}/localizations/{localizationHashedId}`; success 200. Path/query inputs: `mediaHashedId` (path), `localizationHashedId` (path).

**Restrictions:**

Deletes a localization.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting both the `update` and the `edit-transcripts`
permissions on the source media can also be used: `edit-transcripts` finds
the localization and `update` allows deleting it.

**Source:** [Delete Localization](https://docs.wistia.com/reference/delete_medias-mediahashedid-localizations-localizationhashedid.md), pinned SHA-256 `b6a0e7df666385a66fe9aaa6636b4d1d6b68f4092b4179bca4ae3db0be9a4e79`. See the [inventory](../rewrite/operations.json) for exact schemas, limits and error statuses. Offline contract: `tests/Contract/Data/DeleteLocalizationTest.php`; behavioral edge cases: `tests/Contract/Data/AccessibilityBehaviorTest.php`.

## get_media-extended-audio-descriptions

**API:** `DataExtendedAudioDescriptionsService::listMediaExtendedAudioDescriptions(Request\Data\ListMediaExtendedAudioDescriptionsRequest): Result\Data\ListMediaExtendedAudioDescriptionsResult`.

**Wire:** `GET /media_extended_audio_descriptions`; success 200. Path/query inputs: `page` (query), `per_page` (query), `cursor` (query), `hashed_ids[]` (query), `sort_by` (query), `sort_direction` (query).

**Restrictions:**

Lists all extended audio descriptions belonging to the account. Supports pagination and sorting.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization naming a media (any permission) can also be used; it lists
the extended audio descriptions of the media the token names.

**Source:** [List Media Extended Audio Descriptions](https://docs.wistia.com/reference/get_media-extended-audio-descriptions.md), pinned SHA-256 `2b35a68f3ac5876075fd42b050b0c9cbb0b3e12d7afd6385233b93e53f8ff6dd`. See the [inventory](../rewrite/operations.json) for exact schemas, limits and error statuses. Offline contract: `tests/Contract/Data/ListMediaExtendedAudioDescriptionsTest.php`; behavioral edge cases: `tests/Contract/Data/AccessibilityBehaviorTest.php`.

## get_media-extended-audio-descriptions-id

**API:** `DataExtendedAudioDescriptionsService::showMediaExtendedAudioDescription(Request\Data\ShowMediaExtendedAudioDescriptionRequest): Result\Data\ShowMediaExtendedAudioDescriptionResult`.

**Wire:** `GET /media_extended_audio_descriptions/{id}`; success 200. Path/query inputs: `id` (path).

**Restrictions:**

Retrieves a single extended audio description by its hashed id, including download links.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization naming the extended audio description's media (any
permission) can also be used.

**Source:** [Show Media Extended Audio Description](https://docs.wistia.com/reference/get_media-extended-audio-descriptions-id.md), pinned SHA-256 `ab717900bf2f97c2326ca3c2a3262fb63fe5f9506ba984df66660c6d1574cfb8`. See the [inventory](../rewrite/operations.json) for exact schemas, limits and error statuses. Offline contract: `tests/Contract/Data/ShowMediaExtendedAudioDescriptionTest.php`; behavioral edge cases: `tests/Contract/Data/AccessibilityBehaviorTest.php`.

## delete_media-extended-audio-descriptions-id

**API:** `DataExtendedAudioDescriptionsService::deleteMediaExtendedAudioDescription(Request\Data\DeleteMediaExtendedAudioDescriptionRequest): Result\Data\DeleteMediaExtendedAudioDescriptionResult`.

**Wire:** `DELETE /media_extended_audio_descriptions/{id}`; success 200. Path/query inputs: `id` (path).

**Restrictions:**

Deletes an extended audio description by its hashed id.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `update` permission on the extended audio
description's media can also be used.

**Source:** [Delete Media Extended Audio Description](https://docs.wistia.com/reference/delete_media-extended-audio-descriptions-id.md), pinned SHA-256 `8564908d564ba370e0dc4d31c2867f435094f44286640c5df45ca04006314b2a`. See the [inventory](../rewrite/operations.json) for exact schemas, limits and error statuses. Offline contract: `tests/Contract/Data/DeleteMediaExtendedAudioDescriptionTest.php`; behavioral edge cases: `tests/Contract/Data/AccessibilityBehaviorTest.php`.

## get_media-extended-audio-descriptions-order-status-id

**API:** `DataExtendedAudioDescriptionsService::getOrderStatus(Request\Data\GetOrderStatusRequest): Result\Data\GetOrderStatusResult`.

**Wire:** `GET /media_extended_audio_descriptions/order_status/{id}`; success 200. Path/query inputs: `id` (path).

**Restrictions:**

Returns the current status of an extended audio description order. Use the order id returned
from the order endpoint to poll for status updates.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization naming the order's media (any permission) can also be
used.

**Source:** [Get Order Status](https://docs.wistia.com/reference/get_media-extended-audio-descriptions-order-status-id.md), pinned SHA-256 `020fa38e86fed55c47f9d30712472fe4cb3eddc669a730eec5098692aaf7812c`. See the [inventory](../rewrite/operations.json) for exact schemas, limits and error statuses. Offline contract: `tests/Contract/Data/GetOrderStatusTest.php`; behavioral edge cases: `tests/Contract/Data/AccessibilityBehaviorTest.php`.


## Folders, sharing and subfolders

P09 implements the following 17 operations with typed nested results and offline synthetic contracts. See the [usage guide](../folders.md) for inputs, pagination, job handoff and safety restrictions. No live verification is claimed.

## get_folders

**API:** `DataFoldersService::listFolders(Request\Data\ListFoldersRequest): Result\Data\ListFoldersResult`.

**Wire:** `GET /folders`; success 200; Bearer authentication and API version `2026-09`. Supports offset or nested record-cursor pagination, with filters and sorting preserved.

**Restrictions:**

Lists folders (previously called projects) belonging to the account.
My Library folders are not included.

For tokens scoped to a specific user (`all:delegate_to_contact_permissions`),
results are limited to folders that user can see in their content library:
folders shared with them directly, through a contact group, or with the
whole account (owners and managers see every folder). Public (unlocked)
folders the user has no sharing on remain viewable by link but are not
listed.

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope can also be
used. Results are limited to the folders its authorizations name (any
permission granted on a folder qualifies it), filtered as they would be
for the contact the token was created for.

**Source:** [List Folders](https://docs.wistia.com/reference/get_folders.md), pinned SHA-256 `98a840809dc0156d03bf707eb5a97223d5beac9c60b97aaf398c67935b197dd6`. Full request/response schemas and error statuses remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/ListFoldersTest.php`; synthetic coverage is not live verification.

## post_folders

**API:** `DataFoldersService::createFolder(Request\Data\CreateFolderRequest): Result\Data\CreateFolderResult`.

**Wire:** `POST /folders`; success 201; Bearer authentication and API version `2026-09`. No pagination is documented for this operation.

**Restrictions:**

Creates a new folder (previously called project). If the folder is created successfully the Location HTTP header will point to the new folder.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
`account` authorization granting the `create-folders` permission can also
be used. The folder's creator is the contact behind the token (the account
owner for a token minted from an account-level token); `adminEmail` selects
the folder's administrator (defaults to the account owner). `personalLibrary`
creates the folder inside the My Library of the contact behind the token.
The new folder is not covered by the token that created it, so follow-up
requests need a token whose authorizations name the returned hashed id.

**Source:** [Create Folder](https://docs.wistia.com/reference/post_folders.md), pinned SHA-256 `b1a6d94516fb04fc68e51340d508bad46c646428e35e04379068509b20173463`. Full request/response schemas and error statuses remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/CreateFolderTest.php`; synthetic coverage is not live verification.

## get_folders-id

**API:** `DataFoldersService::showFolder(Request\Data\ShowFolderRequest): Result\Data\ShowFolderResult`.

**Wire:** `GET /folders/{id}`; success 200; Bearer authentication and API version `2026-09`. No pagination is documented for this operation.

**Restrictions:**

Retrieves a single folder (previously called project).

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization for this folder can also be used; any permission granted on a
folder allows showing it.

**Source:** [Show Folder](https://docs.wistia.com/reference/get_folders-id.md), pinned SHA-256 `42dfaba4f7c82865051de57ba582af3024cd729652ec6aa91bfd24a1f7ed7168`. Full request/response schemas and error statuses remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/ShowFolderTest.php`; synthetic coverage is not live verification.

## put_folders-id

**API:** `DataFoldersService::updateFolder(Request\Data\UpdateFolderRequest): Result\Data\UpdateFolderResult`.

**Wire:** `PUT /folders/{id}`; success 200; Bearer authentication and API version `2026-09`. No pagination is documented for this operation.

**Restrictions:**

Updates a folder (previously called project)

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `update` permission on this folder can also be
used. The `update` permission also allows creating, renaming and deleting
the folder's subfolders and using the folder as the destination when
moving or bulk-copying media the token may update.

**Source:** [Update Folder](https://docs.wistia.com/reference/put_folders-id.md), pinned SHA-256 `32077c582364d6192551e2dde483bbe407aacb9350985bb613b66e529f208982`. Full request/response schemas and error statuses remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/UpdateFolderTest.php`; synthetic coverage is not live verification.

## delete_folders-id

**API:** `DataFoldersService::deleteFolder(Request\Data\DeleteFolderRequest): Result\Data\DeleteFolderResult`.

**Wire:** `DELETE /folders/{id}`; success 200; Bearer authentication and API version `2026-09`. No pagination is documented for this operation.

**Restrictions:**

Deletes a folder (previously called project) and the media inside it.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `destroy` permission on this folder can also be
used.

**Source:** [Delete Folder](https://docs.wistia.com/reference/delete_folders-id.md), pinned SHA-256 `7775c4334d6437a3bb12d6e70b5a2b5e449f36820ce0edaf5fe8b39d7c821391`. Full request/response schemas and error statuses remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/DeleteFolderTest.php`; synthetic coverage is not live verification.

## post_folders-id-copy

**API:** `DataFoldersService::copyFolder(Request\Data\CopyFolderRequest): Result\Data\CopyFolderResult`.

**Wire:** `POST /folders/{id}/copy`; success 202; Bearer authentication and API version `2026-09`. No pagination is documented for this operation.

**Restrictions:**

This copies a folder (previously called project) and all its media and subfolders asynchronously in a background job.

This method does not copy the folder’s sharing information (i.e. users that could see the old folder will not automatically be able to see the new one).

For the request you can specify the owner of a new folder by passing an optional parameter. The person you specify must be a Manager in the account.

The body of the response will contain an object representing the background job that was created.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

**Source:** [Copy Folder](https://docs.wistia.com/reference/post_folders-id-copy.md), pinned SHA-256 `aba2c59f8062245fffe873fb20e02d2d719fcc7734c12376e91c8bd5b8c37ff7`. Full request/response schemas and error statuses remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/CopyFolderTest.php`; synthetic coverage is not live verification.

## get_folders-folderid-sharings

**API:** `DataFolderSharingsService::listFolderSharings(Request\Data\ListFolderSharingsRequest): Result\Data\ListFolderSharingsResult`.

**Wire:** `GET /folders/{folderId}/sharings`; success 200; Bearer authentication and API version `2026-09`. Supports offset or nested record-cursor pagination, with filters and sorting preserved.

**Restrictions:**

Lists the sharings of contacts and contact groups on a folder.

## Requires api token with one of the following permissions
```
Read all data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization naming this folder (any permission) can also be used; it
lists the folder's sharings.

**Source:** [List Folder Sharings](https://docs.wistia.com/reference/get_folders-folderid-sharings.md), pinned SHA-256 `faecaad4061a85fc3d36df12ccf5407f451cdbd6b3e5abf7f40a2cd98118fe0d`. Full request/response schemas and error statuses remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/ListFolderSharingsTest.php`; synthetic coverage is not live verification.

## post_folders-folderid-sharings

**API:** `DataFolderSharingsService::createFolderSharing(Request\Data\CreateFolderSharingRequest): Result\Data\CreateFolderSharingResult`.

**Wire:** `POST /folders/{folderId}/sharings`; success 201; Bearer authentication and API version `2026-09`. No pagination is documented for this operation.

**Restrictions:**

Creates a new sharing object for a folder by specifying the email of the person to share with and other optional parameters.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `share` permission on this folder can also
be used.

**Source:** [Create Folder Sharing](https://docs.wistia.com/reference/post_folders-folderid-sharings.md), pinned SHA-256 `2a4e6d80a9a57af5e2004663c4beabba82906a024691fde6bc9ad59c1b1d288d`. Full request/response schemas and error statuses remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/CreateFolderSharingTest.php`; synthetic coverage is not live verification.

## get_folders-folderid-sharings-sharingid

**API:** `DataFolderSharingsService::showFolderSharing(Request\Data\ShowFolderSharingRequest): Result\Data\ShowFolderSharingResult`.

**Wire:** `GET /folders/{folderId}/sharings/{sharingId}`; success 200; Bearer authentication and API version `2026-09`. No pagination is documented for this operation.

**Restrictions:**

Retrieves the details of a specific sharing object for a given folder.

## Requires api token with one of the following permissions
```
Read all data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization naming this folder (any permission) can also be used.

**Source:** [Show Folder Sharing](https://docs.wistia.com/reference/get_folders-folderid-sharings-sharingid.md), pinned SHA-256 `bc295d14c8feb76101523943bcc55b59e1a43b71eb7e593d6270d3b647a74d72`. Full request/response schemas and error statuses remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/ShowFolderSharingTest.php`; synthetic coverage is not live verification.

## put_folders-folderid-sharings-sharingid

**API:** `DataFolderSharingsService::updateFolderSharing(Request\Data\UpdateFolderSharingRequest): Result\Data\UpdateFolderSharingResult`.

**Wire:** `PUT /folders/{folderId}/sharings/{sharingId}`; success 200; Bearer authentication and API version `2026-09`. No pagination is documented for this operation.

**Restrictions:**

Updates a sharing on a folder.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `update` permission on this folder can also
be used.

**Source:** [Update Folder Sharing](https://docs.wistia.com/reference/put_folders-folderid-sharings-sharingid.md), pinned SHA-256 `cce80ecaed603d93523dd9416e2f5a6b3abe3f61988509f766b300c596616b8a`. Full request/response schemas and error statuses remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/UpdateFolderSharingTest.php`; synthetic coverage is not live verification.

## delete_folders-folderid-sharings-sharingid

**API:** `DataFolderSharingsService::deleteFolderSharing(Request\Data\DeleteFolderSharingRequest): Result\Data\DeleteFolderSharingResult`.

**Wire:** `DELETE /folders/{folderId}/sharings/{sharingId}`; success 200; Bearer authentication and API version `2026-09`. No pagination is documented for this operation.

**Restrictions:**

Deletes a sharing on a folder.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `update` permission on this folder can also
be used.

**Source:** [Delete Folder Sharing](https://docs.wistia.com/reference/delete_folders-folderid-sharings-sharingid.md), pinned SHA-256 `95ac057931e7dcafff06a7cd2a5a75b23e4e156c4740d9ecd670c8c58d63f30d`. Full request/response schemas and error statuses remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/DeleteFolderSharingTest.php`; synthetic coverage is not live verification.

## get_folders-folderid-subfolders

**API:** `DataSubfoldersService::listSubfolders(Request\Data\ListSubfoldersRequest): Result\Data\ListSubfoldersResult`.

**Wire:** `GET /folders/{folderId}/subfolders`; success 200; Bearer authentication and API version `2026-09`. Supports offset or nested record-cursor pagination, with filters and sorting preserved.

**Restrictions:**

Lists subfolders in a specific folder.

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization naming this folder (any permission) can also be used; it
lists the folder's subfolders.

**Source:** [List Subfolders](https://docs.wistia.com/reference/get_folders-folderid-subfolders.md), pinned SHA-256 `40f58db2f98239e4d96594e47d9b8b6d98826f5f3a457b3cb0861720a8fb98ca`. Full request/response schemas and error statuses remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/ListSubfoldersTest.php`; synthetic coverage is not live verification.

## post_folders-folderid-subfolders

**API:** `DataSubfoldersService::createSubfolder(Request\Data\CreateSubfolderRequest): Result\Data\CreateSubfolderResult`.

**Wire:** `POST /folders/{folderId}/subfolders`; success 201; Bearer authentication and API version `2026-09`. No pagination is documented for this operation.

**Restrictions:**

Creates a new subfolder within a folder. The subfolder will be created with the next available position.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `update` permission on this folder can also
be used.

**Source:** [Create Subfolder](https://docs.wistia.com/reference/post_folders-folderid-subfolders.md), pinned SHA-256 `8b77cf4dbd5adedea40caa1cd8e8969b30ee6f3653b6610733e5d7a02c1218d2`. Full request/response schemas and error statuses remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/CreateSubfolderTest.php`; synthetic coverage is not live verification.

## get_folders-folderid-subfolders-subfolderid

**API:** `DataSubfoldersService::showSubfolder(Request\Data\ShowSubfolderRequest): Result\Data\ShowSubfolderResult`.

**Wire:** `GET /folders/{folderId}/subfolders/{subfolderId}`; success 200; Bearer authentication and API version `2026-09`. No pagination is documented for this operation.

**Restrictions:**

Retrieves detailed information about a specific subfolder, including all media contained within it.

## Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization naming this folder (any permission) can also be used. The
embedded media are limited to those the token's authorizations name.

**Source:** [Show Subfolder](https://docs.wistia.com/reference/get_folders-folderid-subfolders-subfolderid.md), pinned SHA-256 `cb4f5390685b6b12c95dd04945df79ba5e89cd40113122998c9e9dade77d3a71`. Full request/response schemas and error statuses remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/ShowSubfolderTest.php`; synthetic coverage is not live verification.

## put_folders-folderid-subfolders-subfolderid

**API:** `DataSubfoldersService::updateSubfolder(Request\Data\UpdateSubfolderRequest): Result\Data\UpdateSubfolderResult`.

**Wire:** `PUT /folders/{folderId}/subfolders/{subfolderId}`; success 200; Bearer authentication and API version `2026-09`. No pagination is documented for this operation.

**Restrictions:**

Updates a subfolder's name and/or description.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `update` permission on this folder can also
be used.

**Source:** [Update Subfolder](https://docs.wistia.com/reference/put_folders-folderid-subfolders-subfolderid.md), pinned SHA-256 `403331269675c5a494d439e9803f7e038a58bd94f81a9c6595ee26f4d4fb0c85`. Full request/response schemas and error statuses remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/UpdateSubfolderTest.php`; synthetic coverage is not live verification.

## delete_folders-folderid-subfolders-subfolderid

**API:** `DataSubfoldersService::deleteSubfolder(Request\Data\DeleteSubfolderRequest): Result\Data\DeleteSubfolderResult`.

**Wire:** `DELETE /folders/{folderId}/subfolders/{subfolderId}`; success 200; Bearer authentication and API version `2026-09`. No pagination is documented for this operation.

**Restrictions:**

Deletes one subfolder and moves its media to the folder's root level. The subfolder is soft-deleted and is no longer accessible via the API. To delete multiple subfolders and their media, use the Bulk Delete Subfolders endpoint.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `update` permission on this folder can also
be used.

**Source:** [Delete Subfolder](https://docs.wistia.com/reference/delete_folders-folderid-subfolders-subfolderid.md), pinned SHA-256 `4cbbe57067801bf82bcc06bb4cd46822d23594fd8c7cdfcd6496f628830eecd9`. Full request/response schemas and error statuses remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/DeleteSubfolderTest.php`; synthetic coverage is not live verification.

## delete_folders-folderid-subfolders-bulk-delete

**API:** `DataSubfoldersService::bulkDeleteSubfolders(Request\Data\BulkDeleteSubfoldersRequest): Result\Data\BulkDeleteSubfoldersResult`.

**Wire:** `DELETE /folders/{folderId}/subfolders/bulk_delete`; success 200; Bearer authentication and API version `2026-09`. No pagination is documented for this operation.

**Restrictions:**

Deletes multiple subfolders asynchronously. Their media is also soft-deleted and can be restored from the trash by an account owner or manager until it is purged. To keep the media, use the Delete Subfolder endpoint, which moves it to the folder's root level.

## Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `update` permission on this folder can also
be used.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `update` permission on the folder can also be
used.

**Source:** [Bulk Delete Subfolders](https://docs.wistia.com/reference/delete_folders-folderid-subfolders-bulk-delete.md), pinned SHA-256 `4f0c7f4bee803583c4720863365ffa02748466972989ab5490594ae07b720130`. Full request/response schemas and error statuses remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/BulkDeleteSubfoldersTest.php`; synthetic coverage is not live verification.

## Channels, collaborators and episodes

P10 implements these 16 operations offline. See the [channel guide](../channels.md) for inputs/results, scheduling, null semantics, pagination and safety. All requests use the modern Data host and version `2026-09`; all success bodies are JSON. No live access or plan entitlement is verified.

## get_channels

**API:** `DataChannelsService::listChannels(Request\Data\ListChannelsRequest): Result\Data\ListChannelsResult`.

**Wire:** `GET /channels`; success **200**; Bearer authentication and pinned version `2026-09`. Returns `data: list<Channels\Channel>` and `status`.

**Inputs:** `cursor` (query/path, optional), `page` (query/path, optional), `per_page` (query/path, optional), `sort_by` (query/path, optional), `sort_direction` (query/path, optional), `hashed_ids[]` (query/path, optional).

Individual pages and `paginateChannels` support offset/nested Data cursors; preserve parent IDs, filters, sorting and direction. See the channel guide for endpoint-specific cursor-sort restrictions.

**Restrictions:**

Lists all Channels belonging to an account. This endpoint can also be used to
do a batch fetch based off of the hashed id.

### Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization naming a channel (any permission) can also be used; it
lists the channels the token names.


**Source:** [List Channels](https://docs.wistia.com/reference/get_channels.md), pinned SHA-256 `9bcb2b430b828ca5fb45e58e808c8b59511ddba2e30bb93dc91c99af56ca9dea`. Full schemas and status-specific errors remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/ListChannelsTest.php`. Not marked experimental in the pinned source; this is not a stability or live-verification claim.

## post_channels

**API:** `DataChannelsService::createChannel(Request\Data\CreateChannelRequest): Result\Data\CreateChannelResult`.

**Wire:** `POST /channels`; success **201**; Bearer authentication and pinned version `2026-09`. Returns `data: Channels\Channel` and `status`.

**Inputs:** `name` (JSON body, optional), `description` (JSON body, optional), `auto_publish_enabled` (JSON body, optional), `podcast_enabled` (JSON body, optional), `custom_url` (JSON body, optional), `podcast_settings` (JSON body, optional).

**Restrictions:**

Creates a channel.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `create-channels` permission on the account
can also be used.


**Source:** [Create Channel](https://docs.wistia.com/reference/post_channels.md), pinned SHA-256 `5b317f0b7859b1de56d6daf8ab20b8d956601d8c3483f51f3360a1a034b4a903`. Full schemas and status-specific errors remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/CreateChannelTest.php`. Not marked experimental in the pinned source; this is not a stability or live-verification claim.

## get_channels-channelhashedid

**API:** `DataChannelsService::showChannel(Request\Data\ShowChannelRequest): Result\Data\ShowChannelResult`.

**Wire:** `GET /channels/{channelHashedId}`; success **200**; Bearer authentication and pinned version `2026-09`. Returns `data: Channels\Channel` and `status`.

**Inputs:** `channelHashedId` (query/path, required).

**Restrictions:**

Returns the Channel associated with the hashedId.

### Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization naming this channel (any permission) can also be used.


**Source:** [Show Channel](https://docs.wistia.com/reference/get_channels-channelhashedid.md), pinned SHA-256 `eea851333f53c9f8bb7dd173cf62250d0ec7cbbb871859a108e7bd66ab4abf6a`. Full schemas and status-specific errors remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/ShowChannelTest.php`. Not marked experimental in the pinned source; this is not a stability or live-verification claim.

## put_channels-channelhashedid

**API:** `DataChannelsService::updateChannel(Request\Data\UpdateChannelRequest): Result\Data\UpdateChannelResult`.

**Wire:** `PUT /channels/{channelHashedId}`; success **200**; Bearer authentication and pinned version `2026-09`. Returns `data: Channels\Channel` and `status`.

**Inputs:** `channelHashedId` (query/path, required), `name` (JSON body, optional), `description` (JSON body, optional), `auto_publish_enabled` (JSON body, optional), `podcast_enabled` (JSON body, optional), `custom_url` (JSON body, optional), `podcast_settings` (JSON body, optional).

**Restrictions:**

Updates a channel.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `update` permission on this channel can also
be used. The `update` permission also allows managing the channel's
episodes and collaborators.


**Source:** [Update Channel](https://docs.wistia.com/reference/put_channels-channelhashedid.md), pinned SHA-256 `06d5e1d1d9a8ed4844aa18a9c2a527744e28ad9ee8224041b3f4160015b507b6`. Full schemas and status-specific errors remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/UpdateChannelTest.php`. Not marked experimental in the pinned source; this is not a stability or live-verification claim.

## delete_channels-channelhashedid

**API:** `DataChannelsService::deleteChannel(Request\Data\DeleteChannelRequest): Result\Data\DeleteChannelResult`.

**Wire:** `DELETE /channels/{channelHashedId}`; success **200**; Bearer authentication and pinned version `2026-09`. Returns `data: Channels\Channel` and `status`.

**Inputs:** `channelHashedId` (query/path, required).

**Restrictions:**

Deletes a channel.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `destroy` permission on this channel can also
be used.


**Source:** [Delete Channel](https://docs.wistia.com/reference/delete_channels-channelhashedid.md), pinned SHA-256 `119ad3220ab04168cc64e9f1d5bb9608bfd79bf25d0bb46cd395696417083d50`. Full schemas and status-specific errors remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/DeleteChannelTest.php`. Not marked experimental in the pinned source; this is not a stability or live-verification claim.

## get_channels-channelhashedid-collaborators

**API:** `DataChannelCollaboratorsService::listChannelCollaborators(Request\Data\ListChannelCollaboratorsRequest): Result\Data\ListChannelCollaboratorsResult`.

**Wire:** `GET /channels/{channelHashedId}/collaborators`; success **200**; Bearer authentication and pinned version `2026-09`. Returns `data: list<Channels\ChannelCollaborator>` and `status`.

**Inputs:** `channelHashedId` (query/path, required), `page` (query/path, optional), `per_page` (query/path, optional), `cursor` (query/path, optional), `sort_by` (query/path, optional), `sort_direction` (query/path, optional).

Individual pages and `paginateChannelCollaborators` support offset/nested Data cursors; preserve parent IDs, filters, sorting and direction. See the channel guide for endpoint-specific cursor-sort restrictions.

**Restrictions:**

Lists the collaborators (contacts and contact groups) that have been granted access to a channel.

Results are scoped to what the authenticated user is allowed to see: account owners and managers see all collaborators, channel admins see all collaborators on their channels, and everyone else sees only the roles that grant them access.

### Requires api token with one of the following permissions
```
Read all data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `update` permission on this channel can also
be used; a token granted only `show` on the channel lists no
collaborators.


**Source:** [List Channel Collaborators](https://docs.wistia.com/reference/get_channels-channelhashedid-collaborators.md), pinned SHA-256 `6afb3a6d4d9f0dc246b82646fdfec081215071967228bdcecbb57793fb235170`. Full schemas and status-specific errors remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/ListChannelCollaboratorsTest.php`. Not marked experimental in the pinned source; this is not a stability or live-verification claim.

## post_channels-channelhashedid-collaborators

**API:** `DataChannelCollaboratorsService::createChannelCollaborator(Request\Data\CreateChannelCollaboratorRequest): Result\Data\CreateChannelCollaboratorResult`.

**Wire:** `POST /channels/{channelHashedId}/collaborators`; success **201**; Bearer authentication and pinned version `2026-09`. Returns `data: Channels\ChannelCollaborator` and `status`.

**Inputs:** `channelHashedId` (query/path, required), `email` (JSON body, required), `role` (JSON body, required).

**Restrictions:**

Invites a collaborator to a channel by specifying their email address and role. Creates a new contact if one doesn't exist with that email.

### Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `update` permission on this channel can also
be used.


**Source:** [Create Channel Collaborator](https://docs.wistia.com/reference/post_channels-channelhashedid-collaborators.md), pinned SHA-256 `bbdbd6197f8a6a4b0fb78d11d08a8ea782719993ed1856d5399b082ad7bb826e`. Full schemas and status-specific errors remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/CreateChannelCollaboratorTest.php`. Not marked experimental in the pinned source; this is not a stability or live-verification claim.

## delete_channels-channelhashedid-collaborators-id

**API:** `DataChannelCollaboratorsService::deleteChannelCollaborator(Request\Data\DeleteChannelCollaboratorRequest): Result\Data\DeleteChannelCollaboratorResult`.

**Wire:** `DELETE /channels/{channelHashedId}/collaborators/{id}`; success **200**; Bearer authentication and pinned version `2026-09`. Returns `data: Channels\ChannelCollaborator` and `status`.

**Inputs:** `channelHashedId` (query/path, required), `id` (query/path, required).

**Restrictions:**

Removes a collaborator's access to a channel.

### Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `update` permission on this channel can also
be used.


**Source:** [Delete Channel Collaborator](https://docs.wistia.com/reference/delete_channels-channelhashedid-collaborators-id.md), pinned SHA-256 `39f61497d446377e45530bf3406ce2e85b2a6d186bbbc1e8006c6da4236c1681`. Full schemas and status-specific errors remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/DeleteChannelCollaboratorTest.php`. Not marked experimental in the pinned source; this is not a stability or live-verification claim.

## get_channels-channelhashedid-channel-episodes-channelepisodeid

**API:** `DataChannelEpisodesService::showChannelEpisode(Request\Data\ShowChannelEpisodeRequest): Result\Data\ShowChannelEpisodeResult`.

**Wire:** `GET /channels/{channelHashedId}/channel_episodes/{channelEpisodeId}`; success **200**; Bearer authentication and pinned version `2026-09`. Returns `data: Channels\ChannelEpisode` and `status`.

**Inputs:** `channelHashedId` (query/path, required), `channelEpisodeId` (query/path, required).

**Restrictions:**

Returns the Channel Episode associated with a channel hashed id
and channel episode hashed id.

### Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization naming the episode's channel (any permission) can also be
used.


**Source:** [Show Channel Episode](https://docs.wistia.com/reference/get_channels-channelhashedid-channel-episodes-channelepisodeid.md), pinned SHA-256 `fcec866fe7d86310994bf2b48c4511c2312d3a75c9adb799115ac378e7213950`. Full schemas and status-specific errors remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/ShowChannelEpisodeTest.php`. Not marked experimental in the pinned source; this is not a stability or live-verification claim.

## get_channels-channelhashedid-channel-episodes

**API:** `DataChannelEpisodesService::listChannelEpisodesByChannel(Request\Data\ListChannelEpisodesByChannelRequest): Result\Data\ListChannelEpisodesByChannelResult`.

**Wire:** `GET /channels/{channelHashedId}/channel_episodes`; success **200**; Bearer authentication and pinned version `2026-09`. Returns `data: list<Channels\ChannelEpisode>` and `status`.

**Inputs:** `channelHashedId` (query/path, required), `sort_by` (query/path, optional), `sort_direction` (query/path, optional), `page` (query/path, optional), `per_page` (query/path, optional), `cursor` (query/path, optional), `media_id[]` (query/path, optional), `hashed_ids[]` (query/path, optional), `published` (query/path, optional), `title` (query/path, optional).

Individual pages and `paginateChannelEpisodesByChannel` support offset/nested Data cursors; preserve parent IDs, filters, sorting and direction. See the channel guide for endpoint-specific cursor-sort restrictions.

**Restrictions:**

Lists Channel Episodes belonging to the channel passed in the path.

### Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization naming a channel (any permission) can also be used; it
lists the episodes of the channels the token names.


**Source:** [List Channel Episodes by Channel](https://docs.wistia.com/reference/get_channels-channelhashedid-channel-episodes.md), pinned SHA-256 `3dc6577361084aed2583b8d995be115cbae02f39b5fa08e36a045908466a0aec`. Full schemas and status-specific errors remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/ListChannelEpisodesByChannelTest.php`. Not marked experimental in the pinned source; this is not a stability or live-verification claim.

## post_channels-channelhashedid-channel-episodes

**API:** `DataChannelEpisodesService::createChannelEpisode(Request\Data\CreateChannelEpisodeRequest): Result\Data\CreateChannelEpisodeResult`.

**Wire:** `POST /channels/{channelHashedId}/channel_episodes`; success **200**; Bearer authentication and pinned version `2026-09`. Returns `data: Channels\ChannelEpisode` and `status`.

**Inputs:** `channelHashedId` (query/path, required), `media_id` (JSON body, optional), `title` (JSON body, optional), `description` (JSON body, optional), `summary` (JSON body, optional), `publish_status` (JSON body, optional), `publish_at` (JSON body, optional), `podcast_settings` (JSON body, optional).

**Restrictions:**

Creates a new channel episode in a channel.

### Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `update` permission on this channel can also
be used.


**Source:** [Create Channel Episode](https://docs.wistia.com/reference/post_channels-channelhashedid-channel-episodes.md), pinned SHA-256 `3893b766b5a1671052d721788da1e5a00bd95b7cbaa777947fc2fe5a64780926`. Full schemas and status-specific errors remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/CreateChannelEpisodeTest.php`. Not marked experimental in the pinned source; this is not a stability or live-verification claim.

## get_channel-episodes

**API:** `DataChannelEpisodesService::listChannelEpisodes(Request\Data\ListChannelEpisodesRequest): Result\Data\ListChannelEpisodesResult`.

**Wire:** `GET /channel_episodes`; success **200**; Bearer authentication and pinned version `2026-09`. Returns `data: list<Channels\ChannelEpisode>` and `status`.

**Inputs:** `channel_id` (query/path, optional), `sort_by` (query/path, optional), `sort_direction` (query/path, optional), `page` (query/path, optional), `per_page` (query/path, optional), `cursor` (query/path, optional), `media_id[]` (query/path, optional), `hashed_ids[]` (query/path, optional), `published` (query/path, optional), `title` (query/path, optional).

Individual pages and `paginateChannelEpisodes` support offset/nested Data cursors; preserve parent IDs, filters, sorting and direction. See the channel guide for endpoint-specific cursor-sort restrictions.

**Restrictions:**

Lists Channel Episodes belonging to an account. This endpoint can also be used to
do a batch fetch based off of the hashed id.

### Requires api token with one of the following permissions
```
Read all folder and media data
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization naming a channel (any permission) can also be used; it
lists the episodes of the channels the token names.


**Source:** [List Channel Episodes](https://docs.wistia.com/reference/get_channel-episodes.md), pinned SHA-256 `7d0f457f53c2ae80fc3dc78bc9a6214474c756f423d801e2153c138e3d5e9d8e`. Full schemas and status-specific errors remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/ListChannelEpisodesTest.php`. Not marked experimental in the pinned source; this is not a stability or live-verification claim.

## put_channel-episodes-channelepisodehashedid

**API:** `DataChannelEpisodesService::updateChannelEpisode(Request\Data\UpdateChannelEpisodeRequest): Result\Data\UpdateChannelEpisodeResult`.

**Wire:** `PUT /channel_episodes/{channelEpisodeHashedId}`; success **200**; Bearer authentication and pinned version `2026-09`. Returns `data: Channels\ChannelEpisode` and `status`.

**Inputs:** `channelEpisodeHashedId` (query/path, required), `description` (JSON body, optional), `title` (JSON body, optional), `media_hashed_id` (JSON body, optional), `live_stream_event_hashed_id` (JSON body, optional), `summary` (JSON body, optional), `publish_status` (JSON body, optional), `publish_at` (JSON body, optional), `episode_notes` (JSON body, optional), `podcast_settings` (JSON body, optional).

**Restrictions:**

Updates an existing channel episode in a channel.
### Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `update` permission on the episode's channel
can also be used.


**Source:** [Update Channel Episode](https://docs.wistia.com/reference/put_channel-episodes-channelepisodehashedid.md), pinned SHA-256 `6b8fb5fcd094ddd34c144c045e89b89c910d75e1bcb89dfd726fe8763e56bec8`. Full schemas and status-specific errors remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/UpdateChannelEpisodeTest.php`. Not marked experimental in the pinned source; this is not a stability or live-verification claim.

## delete_channel-episodes-channelepisodehashedid

**API:** `DataChannelEpisodesService::deleteChannelEpisode(Request\Data\DeleteChannelEpisodeRequest): Result\Data\DeleteChannelEpisodeResult`.

**Wire:** `DELETE /channel_episodes/{channelEpisodeHashedId}`; success **200**; Bearer authentication and pinned version `2026-09`. Returns `data: Channels\ChannelEpisode` and `status`.

**Inputs:** `channelEpisodeHashedId` (query/path, required).

**Restrictions:**

Deletes an existing channel episode in a channel.

### Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `update` permission on the episode's channel
can also be used.


**Source:** [Delete Channel Episode](https://docs.wistia.com/reference/delete_channel-episodes-channelepisodehashedid.md), pinned SHA-256 `246c19f960d40bf1c1683a86ba194e3dcdf67ffbaf05324043128c9fe282f455`. Full schemas and status-specific errors remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/DeleteChannelEpisodeTest.php`. Not marked experimental in the pinned source; this is not a stability or live-verification claim.

## put_channel-episodes-channelepisodehashedid-publish

**API:** `DataChannelEpisodesService::publishChannelEpisode(Request\Data\PublishChannelEpisodeRequest): Result\Data\PublishChannelEpisodeResult`.

**Wire:** `PUT /channel_episodes/{channelEpisodeHashedId}/publish`; success **200**; Bearer authentication and pinned version `2026-09`. Returns `data: Channels\ChannelEpisode` and `status`.

**Inputs:** `channelEpisodeHashedId` (query/path, required), `publish_at` (JSON body, optional).

**Restrictions:**

Publishes an existing channel episode in a channel.
### Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `update` permission on the episode's channel
can also be used.


**Source:** [Publish Channel Episode](https://docs.wistia.com/reference/put_channel-episodes-channelepisodehashedid-publish.md), pinned SHA-256 `9ac1b259ed210ec518261c9f4a79d39d9f96c6bab5091e5a44815f9c25753cd7`. Full schemas and status-specific errors remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/PublishChannelEpisodeTest.php`. Not marked experimental in the pinned source; this is not a stability or live-verification claim.

## put_channel-episodes-channelepisodehashedid-unpublish

**API:** `DataChannelEpisodesService::unPublishChannelEpisode(Request\Data\UnPublishChannelEpisodeRequest): Result\Data\UnPublishChannelEpisodeResult`.

**Wire:** `PUT /channel_episodes/{channelEpisodeHashedId}/unpublish`; success **200**; Bearer authentication and pinned version `2026-09`. Returns `data: Channels\ChannelEpisode` and `status`.

**Inputs:** `channelEpisodeHashedId` (query/path, required).

**Restrictions:**

Un-publishes an existing channel episode in a channel.
### Requires api token with one of the following permissions
```
Read, update & delete anything
```

Tokens with the "Act with a team member's permissions" permission
(`all:delegate_to_contact_permissions` scope) can also be used. Requests
made with such a token are authorized using the permissions of the
contact assigned to the token.

An [expiring access token](https://docs.wistia.com/reference/post_expiring-token)
created with the `all:delegate_to_contact_permissions` scope and an
authorization granting the `update` permission on the episode's channel
can also be used.


**Source:** [Un-publish Channel Episode](https://docs.wistia.com/reference/put_channel-episodes-channelepisodehashedid-unpublish.md), pinned SHA-256 `41488451394f6bc327537bc88f363fe7848d4f952054c7661035b278551f8189`. Full schemas and status-specific errors remain in the [inventory](../rewrite/operations.json). Offline contract: `tests/Contract/Data/UnPublishChannelEpisodeTest.php`. Not marked experimental in the pinned source; this is not a stability or live-verification claim.
