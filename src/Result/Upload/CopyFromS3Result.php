<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Upload;

use Automattic\Wistia\Result\Result;
use Automattic\Wistia\Upload\Media;

final readonly class CopyFromS3Result implements Result
{
    public function __construct(public Media $data, public int $status) {}
    public function __debugInfo(): array { return []; }
}
