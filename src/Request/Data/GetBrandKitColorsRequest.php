<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;
use Automattic\Wistia\Brands\BrandAttributes;

final readonly class GetBrandKitColorsRequest
{
    public function __construct(

    ) {

    }
    public function query(): Query
    {
        $pairs = [];

        return new Query($pairs);
    }
    public function body(): ?Body
    {
        return null;
    }
    public function __debugInfo(): array { return []; }
}
