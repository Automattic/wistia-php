<?php
declare(strict_types=1);

namespace Automattic\Wistia\Jobs;

use Automattic\Wistia\DataBackgroundJobStatusService;
use Automattic\Wistia\Request\Data\ShowBackgroundJobStatusRequest;

/** Repeated read-only status checks, never a retry of the operation that created the job. */
final readonly class BackgroundJobPoller
{
    public function __construct(#[\SensitiveParameter] private DataBackgroundJobStatusService $service,
        private PollingRuntime $runtime = new SystemPollingRuntime()) {}

    public function poll(#[\SensitiveParameter] ShowBackgroundJobStatusRequest $request,
        PollingOptions $options = new PollingOptions()): PollingResult
    {
        $start = $this->runtime->now();
        for ($attempt = 1; ; ++$attempt) {
            $result = $this->service->showBackgroundJobStatus($request);
            $job = $result->backgroundJobStatus;
            $termination = match (true) {
                $job->hasFailures() => PollingTermination::ReportedFailure,
                $job->status === JobStatus::Finished => PollingTermination::Completed,
                $attempt === $options->maxAttempts => PollingTermination::Exhausted,
                default => null,
            };
            if ($termination !== null) {
                return new PollingResult($result, $termination, $attempt, max(0.0, $this->runtime->now() - $start));
            }
            $this->runtime->wait($options->intervalSeconds);
        }
    }
    public function __debugInfo(): array { return []; }
}
