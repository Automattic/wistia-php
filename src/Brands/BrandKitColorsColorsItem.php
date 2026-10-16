<?php
declare(strict_types=1);

namespace Automattic\Wistia\Brands;

use Automattic\Wistia\Media\Wire;

/** Typed response; presentFields distinguishes omission from explicit null. */
final readonly class BrandKitColorsColorsItem
{
    public function __construct(
        #[\SensitiveParameter] public string $name,
        #[\SensitiveParameter] public string $value,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['name', 'value'], ['name', 'value']);
        return new self(
            Wire::string($value->name),
            Wire::string($value->value),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
