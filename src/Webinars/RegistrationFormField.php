<?php
declare(strict_types=1);

namespace Automattic\Wistia\Webinars;

use Automattic\Wistia\Media\Wire;

/** Typed response record; presentFields distinguishes absent fields from null. */
final readonly class RegistrationFormField
{
    public function __construct(
        #[\SensitiveParameter] public ?string $value,
        #[\SensitiveParameter] public ?string $label,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['value', 'label'], []);
        return new self(
            property_exists($value, 'value') ? Wire::string($value->value) : null,
            property_exists($value, 'label') ? Wire::string($value->label) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
