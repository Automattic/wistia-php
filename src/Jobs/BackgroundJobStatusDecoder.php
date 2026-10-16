<?php
declare(strict_types=1);

namespace Automattic\Wistia\Jobs;

use Automattic\Wistia\Http\{JsonDecoder, ResponseDecoder, TransportResponse};
use Automattic\Wistia\Result\Data\ShowBackgroundJobStatusResult;

/** Validates the closed 200 schema of get_background-job-status-backgroundjobstatusid. */
final class BackgroundJobStatusDecoder implements ResponseDecoder
{
    public function decode(#[\SensitiveParameter] TransportResponse $response): ShowBackgroundJobStatusResult
    {
        $root = (new JsonDecoder())->decode($response)->value;
        self::shape($root, ['background_job_status'], ['background_job_status']);
        $job = $root->background_job_status;
        self::shape($job, ['id', 'hashed_id', 'status', 'object'], ['id', 'hashed_id', 'status']);
        self::integer($job->id); self::string($job->hashed_id); self::string($job->status);
        $content = null;
        if (property_exists($job, 'object')) {
            $bulk = $job->object;
            self::shape($bulk, ['type', 'summary', 'results'], ['type', 'summary', 'results']);
            if ($bulk->type !== 'bulk_operation') { self::invalid(); }
            $summary = $bulk->summary;
            self::shape($summary, ['total', 'succeeded', 'failed'], ['total', 'succeeded', 'failed']);
            self::integer($summary->total); self::integer($summary->succeeded); self::integer($summary->failed);
            if (!is_array($bulk->results) || !array_is_list($bulk->results)) { self::invalid(); }
            $actions = [];
            foreach ($bulk->results as $action) {
                self::shape($action, ['position', 'operation', 'resource_type', 'id', 'status', 'error'], ['position', 'status']);
                self::integer($action->position); self::string($action->status);
                foreach (['operation', 'resource_type', 'id', 'error'] as $key) {
                    if (property_exists($action, $key)) { self::string($action->$key); }
                }
                $actions[] = new BulkActionResult($action->position, (BulkActionStatus::tryFrom($action->status) ?? self::invalid()),
                    isset($action->operation) ? (BulkOperation::tryFrom($action->operation) ?? self::invalid()) : null,
                    $action->resource_type ?? null, $action->id ?? null, $action->error ?? null);
            }
            $content = new BulkJobContent(new BulkSummary($summary->total, $summary->succeeded, $summary->failed), $actions);
        }
        return new ShowBackgroundJobStatusResult(new BackgroundJobStatus($job->id, $job->hashed_id, (JobStatus::tryFrom($job->status) ?? self::invalid()), $content));
    }

    private static function shape(#[\SensitiveParameter] mixed $value, array $allowed, array $required): void
    {
        if (!$value instanceof \stdClass || array_diff(array_keys(get_object_vars($value)), $allowed) !== []) { self::invalid(); }
        foreach ($required as $key) { if (!property_exists($value, $key)) { self::invalid(); } }
    }
    private static function integer(#[\SensitiveParameter] mixed $value): void { if (!is_int($value)) { self::invalid(); } }
    private static function string(#[\SensitiveParameter] mixed $value): void { if (!is_string($value)) { self::invalid(); } }
    private static function invalid(): never { throw new \UnexpectedValueException('Invalid background job status response.'); }
}
