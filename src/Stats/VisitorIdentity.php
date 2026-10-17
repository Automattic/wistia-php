<?php
declare(strict_types=1);

namespace Automattic\Wistia\Stats;

use Automattic\Wistia\Media\Wire;

final readonly class VisitorIdentity
{
    public function __construct(
        #[\SensitiveParameter] public string|null $name,
        #[\SensitiveParameter] public string|null $email,
        #[\SensitiveParameter] public VisitorOrganization|null $org,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['name', 'email', 'org'], []);
        return new self(
            property_exists($value, 'name') ? ($value->name === null ? null : (Wire::string($value->name))) : null,
            property_exists($value, 'email') ? ($value->email === null ? null : (Wire::string($value->email))) : null,
            property_exists($value, 'org') ? (VisitorOrganization::fromWire($value->org)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
