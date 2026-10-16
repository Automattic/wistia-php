<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Data;

final class RestoreMediaTest extends MediaOperationContract
{
    protected const OPERATION = 'RestoreMedia';

    public function testContainerAndJobArePreserved(): void
    {
        [$result] = $this->invoke();
        self::assertSame('Folder', $result->data->container->type);
        self::assertSame('folder', $result->data->container->name);
        self::assertSame('folder-hash', $result->data->container->hashedId);
        self::assertFalse(property_exists($result->data->container, 'hashed_id'));
        self::assertSame('job-hash', $result->data->background_job_status->hashed_id);
    }
}
