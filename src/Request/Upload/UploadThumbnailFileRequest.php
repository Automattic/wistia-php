<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Upload;

use Automattic\Wistia\Upload\{Input, LocalFile};

final readonly class UploadThumbnailFileRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $media_id,
        #[\SensitiveParameter] public LocalFile $file,
    ) { Input::id($media_id); }
    public function __debugInfo(): array { return []; }
}
