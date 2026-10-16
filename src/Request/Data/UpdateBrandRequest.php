<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;
use Automattic\Wistia\Brands\BrandAttributes;

final readonly class UpdateBrandRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $brandId,
        #[\SensitiveParameter] public BrandAttributes $attributes = new BrandAttributes(),
    ) {
        Path::segment($brandId); Input::nonempty($brandId);
    }
    public function query(): Query
    {
        $pairs = [];

        return new Query($pairs);
    }
    public function body(): ?Body
    {
        return Body::json($this->attributes);
    }
    public function __debugInfo(): array { return []; }
}
