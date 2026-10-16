<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Data;

final class BulkCopyMediaTest extends MediaOperationContract
{
    protected const OPERATION = 'BulkCopyMedia';

    public function testDestinationAndJobArePreserved(): void
    {
        [$result] = $this->invoke();
        self::assertSame('Folder', $result->data->destination->type);
        self::assertSame('folder', $result->data->destination->name);
        self::assertSame('folder-hash', $result->data->destination->hashedId);
        self::assertFalse(property_exists($result->data->destination, 'hashed_id'));
        self::assertSame('job-hash', $result->data->background_job_status->hashed_id);
    }
}
