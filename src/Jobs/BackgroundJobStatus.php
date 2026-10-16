<?php
declare(strict_types=1);

namespace Automattic\Wistia\Jobs;

final readonly class BackgroundJobStatus
{
    public function __construct(public int $id, #[\SensitiveParameter] public string $hashedId,
        public JobStatus $status, #[\SensitiveParameter] public ?BulkJobContent $object = null) {}
    public function hasFailures(): bool { return $this->status === JobStatus::Failed || ($this->object?->hasFailures() ?? false); }
    public function __debugInfo(): array { return ['status' => $this->status]; }
}
