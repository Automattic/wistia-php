<?php
declare(strict_types=1);

namespace Automattic\Wistia\Bulk;

use Automattic\Wistia\Http\{JsonDecoder, ResponseDecoder, TransportResponse};
use Automattic\Wistia\Jobs\BackgroundJobStatusDecoder;
use Automattic\Wistia\Result\Data\CreateBulkActionsResult;

/** The submission schema has no job object; per-action results belong to Show Job Status. */
final class Decoder implements ResponseDecoder
{
    public function decode(#[\SensitiveParameter] TransportResponse $response): CreateBulkActionsResult
    {
        $value = (new JsonDecoder())->decode($response)->value;
        if (!$value instanceof \stdClass || !isset($value->message) || !is_string($value->message)
            || !isset($value->background_job_status) || !$value->background_job_status instanceof \stdClass
            || array_diff(array_keys(get_object_vars($value)), ['message', 'background_job_status']) !== []
            || array_diff(array_keys(get_object_vars($value->background_job_status)), ['id', 'hashed_id', 'status']) !== []) {
            throw new \UnexpectedValueException('Invalid bulk submission response.');
        }
        $status = (new BackgroundJobStatusDecoder())->decode(new TransportResponse(200,
            ['Content-Type' => ['application/json']], json_encode((object) ['background_job_status' => $value->background_job_status], JSON_THROW_ON_ERROR)));
        return new CreateBulkActionsResult($value->message, $status->backgroundJobStatus);
    }
}
