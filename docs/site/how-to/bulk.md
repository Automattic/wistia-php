# Submit a bulk change

Use `DataBulkActionsService` to submit explicit actions or one change across a scope. Submission is asynchronous and actions can succeed or fail independently.

Start with a [configured client](../tutorials/first-api-call.md#create-the-client), broad write permission and a deliberately chosen set of resources. Restricted expiring tokens with authorizations cannot submit bulk requests.

## Submit explicit updates

This **renames the selected remote media**:

```php
use Automattic\Wistia\Bulk\Action;
use Automattic\Wistia\Bulk\Change;
use Automattic\Wistia\DataBulkActionsService;
use Automattic\Wistia\Request\Data\CreateBulkActionsRequest;

$bulk = new DataBulkActionsService($client);
$result = $bulk->createBulkActions(new CreateBulkActionsRequest(actions: [
    new Action(Change::updateMedia(name: 'Introduction'), id: 'YOUR_FIRST_MEDIA_ID'),
    new Action(Change::updateMedia(name: 'Walkthrough'), id: 'YOUR_SECOND_MEDIA_ID'),
]));
$jobId = $result->backgroundJobStatus->hashedId;
```

The result exposes `backgroundJobStatus` directly, not under `data`. Save its ID and [follow the job](jobs.md); HTTP 202 is submission, not completion.

Each `Action` contains a named typed `Change`. Update/delete/move need a target ID; create actions do not. Caption update/delete target **caption-track IDs**, not media IDs. Customization actions target media hashed IDs.

## Use a scope only when its membership is intended

A `Bulk\Job` can apply a change to explicit IDs or a typed scope, such as a folder. Supply exactly one of request `actions`/`job`, and exactly one job `ids`/`scope`.

Scope membership is resolved remotely using the corresponding list defaults. A folder-scoped media job includes subfolders **and archived media**. The client does not preview or enumerate those members for you. Jobs cannot create records and are unavailable to external contacts.

## Stay within limits

| Limit | Value |
| --- | --- |
| Explicit actions | 1–1000 |
| Explicit job IDs | 1–1000 |
| Encoded JSON body | Fewer than 2,000,000 bytes |
| Remotely resolved scope | At most 5000 records |

Oversized scopes are rejected rather than silently truncated. Narrow the scope or use an explicit set. The body size includes escaping and JSON overhead, not just source text.

## Preserve endpoint semantics

Factories reuse typed media, folder, channel, caption and customization inputs. Partial updates retain omission/null rules. Bulk customization supports concern-specific updates, not broad replacement.

Bulk captions take inline SRT text. No caption purchases, translation, localization generation, audio-description orders or trials are exposed.

**Bulk folder/subfolder deletion soft-deletes their media too.** That differs from deleting one subfolder through its dedicated endpoint, which moves media to the parent root. Review destructive effects before constructing a deletion change.

## Reconcile the outcome

A finished job can still report failed actions. Inspect `hasFailures()` or the poller's `ReportedFailure`, not just `finished`. Preserve successful actions when recovering from a partial failure.

Do not replay a whole batch after an [uncertain submission](errors.md). Requests are sent once; local polling does not cancel processing. Use [Bulk Change](../../../src/Bulk/Change.php) for supported factories and the [bulk service](../../../src/DataBulkActionsService.php) for result types.
