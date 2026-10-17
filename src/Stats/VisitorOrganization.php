<?php
declare(strict_types=1);

namespace Automattic\Wistia\Stats;

use Automattic\Wistia\Media\Wire;

final readonly class VisitorOrganization
{
    public function __construct(
        #[\SensitiveParameter] public string|null $name,
        #[\SensitiveParameter] public string|null $title,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['name', 'title'], []);
        return new self(
            property_exists($value, 'name') ? ($value->name === null ? null : (Wire::string($value->name))) : null,
            property_exists($value, 'title') ? ($value->title === null ? null : (Wire::string($value->title))) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
