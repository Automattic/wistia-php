<?php
declare(strict_types=1);

namespace Automattic\Wistia\Sharing;

use Automattic\Wistia\Media\Wire;

final readonly class ContactReference
{
    public function __construct(
        #[\SensitiveParameter] public ?string $type,
        #[\SensitiveParameter] public ?string $id,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['type', 'id'], []);
        return new self(
            property_exists($value, 'type') ? Wire::oneOf($value->type, ['contact', 'contact_group']) : null,
            property_exists($value, 'id') ? Wire::string($value->id) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
