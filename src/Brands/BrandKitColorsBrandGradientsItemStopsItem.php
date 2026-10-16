<?php
declare(strict_types=1);

namespace Automattic\Wistia\Brands;

use Automattic\Wistia\Media\Wire;

/** Typed response; presentFields distinguishes omission from explicit null. */
final readonly class BrandKitColorsBrandGradientsItemStopsItem
{
    public function __construct(
        #[\SensitiveParameter] public string $color,
        #[\SensitiveParameter] public float $position,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['color', 'position'], ['color', 'position']);
        return new self(
            Wire::string($value->color),
            Wire::number($value->position),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
