<?php
declare(strict_types=1);

namespace Automattic\Wistia\Channels;

use Automattic\Wistia\Media\Wire;

/** Typed response record; presentFields distinguishes absent from null. */
final readonly class ChannelCollaborator
{
    public function __construct(
        #[\SensitiveParameter] public int $id,
        #[\SensitiveParameter] public string $role,
        #[\SensitiveParameter] public string $created_at,
        #[\SensitiveParameter] public ChannelCollaboratorShareRecord $share,
        #[\SensitiveParameter] public ?string $cursor,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['id', 'role', 'created_at', 'share', 'cursor'], ['id', 'role', 'created_at', 'share']);
        return new self(
            Wire::integer($value->id),
            Wire::oneOf(Wire::string($value->role), ['admin', 'viewer']),
            Wire::string($value->created_at),
            ChannelCollaboratorShareRecord::fromWire($value->share),
            property_exists($value, 'cursor') ? ($value->cursor === null ? null : Wire::string($value->cursor)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
