<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Upload;

use Automattic\Wistia\Upload\{LocalFile, UploadOptions};

final readonly class UploadFileRequest
{
    public function __construct(#[\SensitiveParameter] public LocalFile $file, #[\SensitiveParameter] public ?UploadOptions $options = null) {}
    public function __debugInfo(): array { return []; }
}
