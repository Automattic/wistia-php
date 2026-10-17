<?php
declare(strict_types=1);

namespace Automattic\Wistia\Stats;

use Automattic\Wistia\Media\Wire;

final readonly class Visitor
{
    public function __construct(
        #[\SensitiveParameter] public string|null $visitor_key,
        #[\SensitiveParameter] public string|null $created_at,
        #[\SensitiveParameter] public string|null $last_active_at,
        #[\SensitiveParameter] public string|null $last_event_key,
        #[\SensitiveParameter] public string|null $identifying_event_key,
        #[\SensitiveParameter] public int|null $load_count,
        #[\SensitiveParameter] public int|null $play_count,
        #[\SensitiveParameter] public VisitorIdentity|null $visitor_identity,
        #[\SensitiveParameter] public UserAgentDetails|null $user_agent_details,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['visitor_key', 'created_at', 'last_active_at', 'last_event_key', 'identifying_event_key', 'load_count', 'play_count', 'visitor_identity', 'user_agent_details'], []);
        return new self(
            property_exists($value, 'visitor_key') ? (Wire::string($value->visitor_key)) : null,
            property_exists($value, 'created_at') ? (Wire::string($value->created_at)) : null,
            property_exists($value, 'last_active_at') ? (Wire::string($value->last_active_at)) : null,
            property_exists($value, 'last_event_key') ? ($value->last_event_key === null ? null : (Wire::string($value->last_event_key))) : null,
            property_exists($value, 'identifying_event_key') ? ($value->identifying_event_key === null ? null : (Wire::string($value->identifying_event_key))) : null,
            property_exists($value, 'load_count') ? (Wire::integer($value->load_count)) : null,
            property_exists($value, 'play_count') ? (Wire::integer($value->play_count)) : null,
            property_exists($value, 'visitor_identity') ? (VisitorIdentity::fromWire($value->visitor_identity)) : null,
            property_exists($value, 'user_agent_details') ? (UserAgentDetails::fromWire($value->user_agent_details)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
