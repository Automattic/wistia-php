# Implemented Data API reference

Only the operation below is implemented by this reference's P03 delivery. Other Data mappings in the [inventory](../rewrite/operations.json) remain planned; this page does not claim full Data API coverage. Construct services with the shared `Client` directly, not a client accessor or magic dispatch.

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
