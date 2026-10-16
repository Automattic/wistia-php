<?php
declare(strict_types=1);

namespace Automattic\Wistia\Webinars;

use Automattic\Wistia\Media\Wire;

/** Typed response record; presentFields distinguishes absent fields from null. */
final readonly class WebinarCollaboratorShare
{
    public function __construct(
        #[\SensitiveParameter] public int $id,
        #[\SensitiveParameter] public string $name,
        #[\SensitiveParameter] public string $type,
        #[\SensitiveParameter] public ?string $email,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['id', 'name', 'type', 'email'], ['id', 'name', 'type']);
        return new self(
            Wire::integer($value->id),
            Wire::string($value->name),
            Wire::string($value->type),
            property_exists($value, 'email') ? Wire::string($value->email) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
