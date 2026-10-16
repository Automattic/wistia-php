<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

final readonly class ShowBackgroundJobStatusRequest
{
    public function __construct(#[\SensitiveParameter] public string $backgroundJobStatusId)
    {
        if ($backgroundJobStatusId === '') { throw new \InvalidArgumentException('A background job identifier is required.'); }
    }
    public function __debugInfo(): array { return []; }
}
