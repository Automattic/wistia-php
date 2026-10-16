<?php
declare(strict_types=1);

namespace Automattic\Wistia\Webinars;

use Automattic\Wistia\Media\Wire;

/** Typed response record; presentFields distinguishes absent fields from null. */
final readonly class RegistrationChatGroup
{
    public function __construct(
        #[\SensitiveParameter] public ?int $rounded_player_position,
        /** @var list<string>|null */
        #[\SensitiveParameter] public ?array $chats,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['rounded_player_position', 'chats'], []);
        return new self(
            property_exists($value, 'rounded_player_position') ? Wire::integer($value->rounded_player_position) : null,
            property_exists($value, 'chats') ? Wire::items($value->chats, static fn($item) => Wire::string($item)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
