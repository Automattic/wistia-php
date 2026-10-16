<?php
declare(strict_types=1);

namespace Automattic\Wistia\Brands;

use Automattic\Wistia\Media\Wire;

/** Typed response; presentFields distinguishes omission from explicit null. */
final readonly class BrandPlayerLogo
{
    public function __construct(
        #[\SensitiveParameter] public string|null $url,
        #[\SensitiveParameter] public BrandPlayerLogoDimensions|null $dimensions,
        #[\SensitiveParameter] public float|null $size,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['url', 'dimensions', 'size'], []);
        return new self(
            property_exists($value, 'url') ? Wire::string($value->url) : null,
            property_exists($value, 'dimensions') ? ($value->dimensions === null ? null : BrandPlayerLogoDimensions::fromWire($value->dimensions)) : null,
            property_exists($value, 'size') ? ($value->size === null ? null : Wire::number($value->size)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
