<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Jobs\BackgroundJobStatus;
use Automattic\Wistia\Result\Result;

final readonly class CreateBulkActionsResult implements Result
{
    public function __construct(#[\SensitiveParameter] public string $message,
        #[\SensitiveParameter] public BackgroundJobStatus $backgroundJobStatus) {}
    public function __debugInfo(): array { return []; }
}
