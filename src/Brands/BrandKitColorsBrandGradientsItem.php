<?php
declare(strict_types=1);

namespace Automattic\Wistia\Brands;

use Automattic\Wistia\Media\Wire;

/** Typed response; presentFields distinguishes omission from explicit null. */
final readonly class BrandKitColorsBrandGradientsItem
{
    public function __construct(
        #[\SensitiveParameter] public string $name,
        /** @var list<BrandKitColorsBrandGradientsItemStopsItem>|null */
        #[\SensitiveParameter] public array $stops,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['name', 'stops'], ['name', 'stops']);
        return new self(
            Wire::string($value->name),
            Wire::items($value->stops, BrandKitColorsBrandGradientsItemStopsItem::fromWire(...)),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
