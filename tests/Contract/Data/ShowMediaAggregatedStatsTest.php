<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Data;

final class ShowMediaAggregatedStatsTest extends MediaOperationContract
{
    protected const OPERATION = 'ShowMediaAggregatedStats';

    public function testMixedCaseCountersArePreserved(): void
    {
        [$result] = $this->invoke();
        $stats = $result->data->stats;
        self::assertSame([10, 8, 50, 4, 75], [
            $stats->pageLoads, $stats->visitors, $stats->percentOfVisitorsClickingPlay,
            $stats->plays, $stats->averagePercentWatched,
        ]);
        self::assertSame(7, $result->data->id);
        self::assertSame('media-hash', $result->data->hashed_id);
    }
}
