<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;
use Automattic\Wistia\Brands\BrandAttributes;

final readonly class ApplyBrandRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $brandId,
        #[\SensitiveParameter] public string $resource_type,
        #[\SensitiveParameter] public string $resource_id,
        public ?bool $clear_overrides = null,
    ) {
        Path::segment($brandId); Input::nonempty($brandId);
        Input::oneOf($resource_type, ['media', 'folder', 'channel']); Input::nonempty($resource_id);
    }
    public function query(): Query
    {
        $pairs = [];

        return new Query($pairs);
    }
    public function body(): ?Body
    {
        $out = (object) ['resource_type' => $this->resource_type, 'resource_id' => $this->resource_id];
        if ($this->clear_overrides !== null) { $out->clear_overrides = $this->clear_overrides; }
        return Body::json($out);
    }
    public function __debugInfo(): array { return []; }
}
