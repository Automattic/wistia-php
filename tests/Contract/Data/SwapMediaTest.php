<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Data;

final class SwapMediaTest extends MediaOperationContract
{
    protected const OPERATION = 'SwapMedia';

    public function testSwappedMediaAndJobArePreserved(): void
    {
        [$result] = $this->invoke();
        self::assertSame(7, $result->data->media->id);
        self::assertSame('media-hash', $result->data->media->hashed_id);
        self::assertSame('private media', $result->data->media->name);
        self::assertSame(12.5, $result->data->media->duration);
        self::assertSame('private embed', $result->data->media->embedCode);
        self::assertSame('job-hash', $result->data->background_job_status->hashed_id);
    }
}
