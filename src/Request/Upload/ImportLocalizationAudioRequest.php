<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Upload;

use Automattic\Wistia\Upload\Input;

final readonly class ImportLocalizationAudioRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $localization_media_id,
        #[\SensitiveParameter] public string $localization_language,
        #[\SensitiveParameter] public string $url,
        public ?bool $overwrite_existing_localization = null,
    ) { Input::id($localization_media_id); Input::language($localization_language); Input::url($url); }
    public function __debugInfo(): array { return []; }
}
