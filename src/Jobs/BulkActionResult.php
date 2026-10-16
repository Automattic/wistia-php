<?php
declare(strict_types=1);

namespace Automattic\Wistia\Jobs;

final readonly class BulkActionResult
{
    public function __construct(
        public int $position,
        public BulkActionStatus $status,
        public ?BulkOperation $operation = null,
        public ?string $resourceType = null,
        #[\SensitiveParameter] public ?string $id = null,
        #[\SensitiveParameter] public ?string $error = null,
    ) {}
    public function __debugInfo(): array { return ['position' => $this->position, 'status' => $this->status]; }
}
