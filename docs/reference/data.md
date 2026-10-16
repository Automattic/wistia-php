# Implemented Data API reference

This page covers P03's job-status operation and P06's 19 media lifecycle, tags, trims and deleted-media operations. Other Data mappings in the [inventory](../rewrite/operations.json) remain planned; this page does not claim full Data API coverage. See the [media guide](../media.md) for usage, result conventions, pagination, polling and shared restrictions. Construct services with the shared `Client` directly, not a client accessor or magic dispatch.

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
