<?php
declare(strict_types=1);

namespace Automattic\Wistia\Stats;

use Automattic\Wistia\Media\Wire;

final readonly class ConversionData
{
    public function __construct(
        #[\SensitiveParameter] public string|null $email,
        #[\SensitiveParameter] public string|null $first_name,
        #[\SensitiveParameter] public bool|null $is_new_lead,
        #[\SensitiveParameter] public string|null $last_name,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['email', 'first_name', 'is_new_lead', 'last_name'], []);
        return new self(
            property_exists($value, 'email') ? (Wire::string($value->email)) : null,
            property_exists($value, 'first_name') ? (Wire::string($value->first_name)) : null,
            property_exists($value, 'is_new_lead') ? (Wire::boolean($value->is_new_lead)) : null,
            property_exists($value, 'last_name') ? (Wire::string($value->last_name)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
