<?php
declare(strict_types=1);

namespace Automattic\Wistia\Brands;

use Automattic\Wistia\Media\Wire;

/** Typed response; presentFields distinguishes omission from explicit null. */
final readonly class BrandPreload
{
    public function __construct(
        #[\SensitiveParameter] public bool $has_brand_kits,
        #[\SensitiveParameter] public BrandPreloadBrandfetchBrand $brandfetch_brand,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['has_brand_kits', 'brandfetch_brand'], ['has_brand_kits', 'brandfetch_brand']);
        return new self(
            Wire::boolean($value->has_brand_kits),
            BrandPreloadBrandfetchBrand::fromWire($value->brandfetch_brand),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
