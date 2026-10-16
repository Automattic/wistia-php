<?php
declare(strict_types=1);

namespace Automattic\Wistia\Jobs;

final readonly class BulkJobContent
{
    /** @param list<BulkActionResult> $results */
    public function __construct(public BulkSummary $summary, #[\SensitiveParameter] public array $results) {}
    public function hasFailures(): bool
    {
        if ($this->summary->failed > 0) { return true; }
        foreach ($this->results as $result) {
            if ($result->status === BulkActionStatus::Failed) { return true; }
        }
        return false;
    }
    public function __debugInfo(): array { return []; }
}
