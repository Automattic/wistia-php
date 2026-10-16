<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;
use Automattic\Wistia\Customizations\UpdateAppearanceCustomizationsInput;

final readonly class UpdateAppearanceCustomizationsRequest
{
    public function __construct(#[\SensitiveParameter] public string $mediaId, #[\SensitiveParameter] public UpdateAppearanceCustomizationsInput $customizations)
    {
        Path::segment($mediaId); Input::nonempty($mediaId);
    }
    public function query(): Query { return new Query(); }
    public function body(): ?Body { return Body::json($this->customizations); }
    public function __debugInfo(): array { return []; }
}
