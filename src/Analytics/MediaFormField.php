<?php
declare(strict_types=1);

namespace Automattic\Wistia\Analytics;

use Automattic\Wistia\Media\Wire;

final readonly class MediaFormField
{
    public function __construct(
        #[\SensitiveParameter] public string|null $label,
        #[\SensitiveParameter] public string|null $value,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['label','value'], []);
        return new self(
            property_exists($value, 'label') ? Wire::string($value->label) : null,
            property_exists($value, 'value') ? Wire::string($value->value) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
