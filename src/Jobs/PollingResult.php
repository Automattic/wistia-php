<?php
declare(strict_types=1);

namespace Automattic\Wistia\Jobs;

use Automattic\Wistia\Result\Data\ShowBackgroundJobStatusResult;

final readonly class PollingResult
{
    public function __construct(#[\SensitiveParameter] public ShowBackgroundJobStatusResult $last,
        public PollingTermination $termination, public int $attempts, public float $elapsedSeconds) {}
    public function __debugInfo(): array { return ['termination' => $this->termination, 'attempts' => $this->attempts]; }
}
