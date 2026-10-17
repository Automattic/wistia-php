<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Upload;

use Automattic\Wistia\Upload\Input;

final readonly class ImportThumbnailUrlRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $media_id,
        #[\SensitiveParameter] public string $url,
    ) { Input::id($media_id); Input::url($url, image: true); }
    public function __debugInfo(): array { return []; }
}
