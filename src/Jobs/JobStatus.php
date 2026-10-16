<?php
declare(strict_types=1);

namespace Automattic\Wistia\Jobs;

enum JobStatus: string
{
    case Queued = 'queued';
    case Started = 'started';
    case Finished = 'finished';
    case Failed = 'failed';
}
