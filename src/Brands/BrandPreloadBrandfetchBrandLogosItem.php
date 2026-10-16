<?php
declare(strict_types=1);

namespace Automattic\Wistia\Brands;

use Automattic\Wistia\Media\Wire;

/** Typed response; presentFields distinguishes omission from explicit null. */
final readonly class BrandPreloadBrandfetchBrandLogosItem
{
    public function __construct(
        #[\SensitiveParameter] public string|null $theme,
        #[\SensitiveParameter] public string|null $type,
        /** @var list<string>|null */
        #[\SensitiveParameter] public array|null $tags,
        /** @var list<BrandPreloadBrandfetchBrandLogosItemFormatsItem>|null */
        #[\SensitiveParameter] public array|null $formats,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['theme', 'type', 'tags', 'formats'], []);
        return new self(
            property_exists($value, 'theme') ? ($value->theme === null ? null : Wire::string($value->theme)) : null,
            property_exists($value, 'type') ? ($value->type === null ? null : Wire::string($value->type)) : null,
            property_exists($value, 'tags') ? Wire::items($value->tags, Wire::string(...)) : null,
            property_exists($value, 'formats') ? Wire::items($value->formats, BrandPreloadBrandfetchBrandLogosItemFormatsItem::fromWire(...)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
