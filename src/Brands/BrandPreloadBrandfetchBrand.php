<?php
declare(strict_types=1);

namespace Automattic\Wistia\Brands;

use Automattic\Wistia\Media\Wire;

/** Typed response; presentFields distinguishes omission from explicit null. */
final readonly class BrandPreloadBrandfetchBrand
{
    public function __construct(
        #[\SensitiveParameter] public string|null $domain,
        #[\SensitiveParameter] public string|null $primary_color,
        #[\SensitiveParameter] public string|null $logo,
        #[\SensitiveParameter] public string|null $font,
        /** @var list<string>|null */
        #[\SensitiveParameter] public array|null $colors,
        /** @var list<BrandPreloadBrandfetchBrandLogosItem>|null */
        #[\SensitiveParameter] public array|null $logos,
        #[\SensitiveParameter] public int|null $status,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['domain', 'primary_color', 'logo', 'font', 'colors', 'logos', 'status'], ['domain', 'primary_color', 'logo']);
        return new self(
            ($value->domain === null ? null : Wire::string($value->domain)),
            ($value->primary_color === null ? null : Wire::string($value->primary_color)),
            ($value->logo === null ? null : Wire::string($value->logo)),
            property_exists($value, 'font') ? ($value->font === null ? null : Wire::string($value->font)) : null,
            property_exists($value, 'colors') ? Wire::items($value->colors, Wire::string(...)) : null,
            property_exists($value, 'logos') ? Wire::items($value->logos, BrandPreloadBrandfetchBrandLogosItem::fromWire(...)) : null,
            property_exists($value, 'status') ? ($value->status === null ? null : Wire::integer($value->status)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
