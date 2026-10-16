<?php
declare(strict_types=1);
namespace Automattic\Wistia\Result\Data;

use Automattic\Wistia\Accessibility\ListCaptionsByMediaDataItem;
use Automattic\Wistia\Request\Data\CaptionFormat;
use Automattic\Wistia\Result\Result;

final readonly class ShowCaptionsResult implements Result
{
    public function __construct(
        #[\SensitiveParameter] public ListCaptionsByMediaDataItem|string $data,
        public int $status,
        public CaptionFormat $format,
    ) {}
    public function __debugInfo(): array { return []; }
}
