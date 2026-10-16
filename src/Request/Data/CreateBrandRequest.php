<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;
use Automattic\Wistia\Brands\BrandAttributes;

final readonly class CreateBrandRequest
{
    public function __construct(
        #[\SensitiveParameter] public BrandAttributes $attributes,
    ) {
        if (!is_string($attributes->name)) { throw new \InvalidArgumentException('A brand name is required on create.'); }
        Input::nonempty($attributes->name);
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
