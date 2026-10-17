# Follow a background job

Use this when an operation has already returned a background-job identifier. Polling reads status; it never resubmits the operation that created the job.

You need a [configured client](../tutorials/first-api-call.md#create-the-client), permission to read the job and its existing hashed ID.

## Read with a finite bound

```php
use Automattic\Wistia\DataBackgroundJobStatusService;
use Automattic\Wistia\Jobs\BackgroundJobPoller;
use Automattic\Wistia\Jobs\PollingOptions;
use Automattic\Wistia\Jobs\PollingTermination;
use Automattic\Wistia\Request\Data\ShowBackgroundJobStatusRequest;

$poller = new BackgroundJobPoller(new DataBackgroundJobStatusService($client));
$outcome = $poller->poll(
    new ShowBackgroundJobStatusRequest('YOUR_JOB_HASHED_ID'),
    new PollingOptions(maxAttempts: 5, intervalSeconds: 1.0),
);

switch ($outcome->termination) {
    case PollingTermination::Completed:
        // The job finished without reported action failures.
        break;
    case PollingTermination::ReportedFailure:
        // Inspect $outcome->last->backgroundJobStatus for partial results.
        break;
    case PollingTermination::Exhausted:
        // Save the identifier and decide whether to check again later.
        break;
}
```

Replace the placeholder with an existing job ID. There is no wait before the first read. Each request retains the client's finite timeouts. Read errors stop immediately; they are not automatically retried.

## Interpret the outcome

`PollingResult` supplies `last`, `attempts`, `elapsedSeconds` and `termination`.

- **Completed:** the status is finished with no reported failures.
- **ReportedFailure:** the job or an action reports failure. Some actions may already have succeeded.
- **Exhausted:** the last allowed read was still pending. This does not mean the remote job failed.

A finished bulk job can contain action failures. Do not check only its status string.

## Decide when to resume

The attempt count includes the first read. The interval accepts finite values from zero through 86400 seconds. These settings bound reads and waiting; they do not impose an additional total wall-clock deadline.

Stopping polling does not cancel remote work. Persist identifiers and partial outcomes if your application needs later reconciliation. Do not print per-action errors, media IDs or response objects indiscriminately.

Some Upload operations return media rather than a job. Do not invent a job ID for them: follow the [upload guide](uploads.md) and use an appropriate media read to assess processing.
