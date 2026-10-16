<?php
declare(strict_types=1);

namespace Automattic\Wistia\Jobs;

final readonly class BulkSummary
{
    public function __construct(public int $total, public int $succeeded, public int $failed) {}
}
