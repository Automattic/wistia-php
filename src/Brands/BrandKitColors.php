<?php
declare(strict_types=1);

namespace Automattic\Wistia\Brands;

use Automattic\Wistia\Media\Wire;

/** Typed response; presentFields distinguishes omission from explicit null. */
final readonly class BrandKitColors
{
    public function __construct(
        /** @var list<BrandKitColorsColorsItem>|null */
        #[\SensitiveParameter] public array $colors,
        /** @var list<BrandKitColorsBrandGradientsItem>|null */
        #[\SensitiveParameter] public array $brand_gradients,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['colors', 'brand_gradients'], ['colors', 'brand_gradients']);
        return new self(
            Wire::items($value->colors, BrandKitColorsColorsItem::fromWire(...)),
            Wire::items($value->brand_gradients, BrandKitColorsBrandGradientsItem::fromWire(...)),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
