<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Upload;

use Automattic\Wistia\Upload\{Input, UploadOptions};

final readonly class ImportUrlRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $url,
        #[\SensitiveParameter] public ?UploadOptions $options = null,
        public ?bool $low_priority = null,
    ) { Input::url($url); }
    public function __debugInfo(): array { return []; }
}
