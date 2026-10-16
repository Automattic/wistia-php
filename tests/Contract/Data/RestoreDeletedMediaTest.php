<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Data;

final class RestoreDeletedMediaTest extends MediaOperationContract
{
    protected const OPERATION = 'RestoreDeletedMedia';

    public function testRestorationJobValuesAndOptionalityArePreserved(): void
    {
        [$result] = $this->invoke();
        self::assertSame(202, $result->status);
        self::assertSame('queued', $result->data->message);
        self::assertSame(42, $result->data->background_job_status->id);
        self::assertSame('job-hash', $result->data->background_job_status->hashed_id);
        self::assertSame('queued', $result->data->background_job_status->status);
        [$partial] = $this->invoke(body: '{"background_job_status":{"id":42}}');
        self::assertSame(42, $partial->data->background_job_status->id);
        self::assertNull($partial->data->background_job_status->hashed_id);
        self::assertNull($partial->data->background_job_status->status);
        self::assertSame(['id'], $partial->data->background_job_status->presentFields);
        [$empty] = $this->invoke(body: '{}');
        self::assertNull($empty->data->background_job_status);
    }
}
