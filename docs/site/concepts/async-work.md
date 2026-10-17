# Accepted work is not completed work

Some Wistia calls queue a job or accept a file for processing. The HTTP response tells you that the submission was accepted, not that the media is ready or every action succeeded.

## Three separate outcomes

1. **Submission:** the service returns its operation-specific result.
2. **Remote processing:** Wistia runs the queued work independently of your PHP process.
3. **Application reconciliation:** your application checks the final result and stores what it needs.

A successful upload may still be processing and may not be playable. Upload results do not invent a background-job ID. Other operations explicitly return one; follow that ID using [bounded status reads](../how-to/jobs.md).

## Partial success is still possible

Bulk work processes actions independently. A job marked `finished` can contain failed actions. Inspect its failure information or the poller's termination value rather than treating a terminal status alone as success.

Stopping local iteration, polling or a PHP process does not cancel remote work. A local timeout also does not undo a request already sent.

## Uncertain writes need reconciliation

If a state-changing request fails with `remoteOutcomeUncertain`, the remote change may already have happened. A transport interruption, server error or malformed success response can all leave this uncertainty.

Use an appropriate read or application reconciliation process before submitting the operation again. The client deliberately does not retry writes, follow redirects or automatically cancel/replay a batch. [Failure handling](../how-to/errors.md) shows where to make this decision.

## Choose bounds deliberately

Pagination bounds limit traversal. Polling bounds limit status reads. Neither is an application-wide deadline, remote cancellation request or guarantee that all work is complete.

Persist job identifiers and partial results if a workflow must survive process restarts. Your application owns scheduling, recovery and retention; the library does not provide a durable job runner.
