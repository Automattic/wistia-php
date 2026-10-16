<?php
declare(strict_types=1);

namespace Automattic\Wistia\Brands;

use Automattic\Wistia\Media\Wire;

/** Typed response; presentFields distinguishes omission from explicit null. */
final readonly class BrandPreloadBrandfetchBrandLogosItemFormatsItem
{
    public function __construct(
        #[\SensitiveParameter] public string|null $src,
        #[\SensitiveParameter] public string|null $format,
        #[\SensitiveParameter] public string|null $background,
        #[\SensitiveParameter] public int|null $height,
        #[\SensitiveParameter] public int|null $width,
        #[\SensitiveParameter] public int|null $size,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['src', 'format', 'background', 'height', 'width', 'size'], []);
        return new self(
            property_exists($value, 'src') ? Wire::string($value->src) : null,
            property_exists($value, 'format') ? Wire::string($value->format) : null,
            property_exists($value, 'background') ? ($value->background === null ? null : Wire::string($value->background)) : null,
            property_exists($value, 'height') ? ($value->height === null ? null : Wire::integer($value->height)) : null,
            property_exists($value, 'width') ? ($value->width === null ? null : Wire::integer($value->width)) : null,
            property_exists($value, 'size') ? ($value->size === null ? null : Wire::integer($value->size)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
