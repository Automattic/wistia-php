<?php
declare(strict_types=1);

namespace Automattic\Wistia\Account;

use Automattic\Wistia\Media\Wire;

final readonly class CurrentToken
{
    public function __construct(
        #[\SensitiveParameter] public string $type,
        #[\SensitiveParameter] public CurrentTokenApplication|null $application,
        #[\SensitiveParameter] public array $scopes,
        #[\SensitiveParameter] public string|null $name,
        #[\SensitiveParameter] public string|null $expires_at,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['type', 'application', 'scopes', 'name', 'expires_at'], ['type', 'application', 'scopes', 'name']);
        return new self(
            Wire::oneOf(Wire::string($value->type), ['permanent', 'expiring', 'oauth']),
            ($value->application === null ? null : CurrentTokenApplication::fromWire($value->application)),
            Wire::items($value->scopes, Wire::string(...)),
            ($value->name === null ? null : Wire::string($value->name)),
            property_exists($value, 'expires_at') ? ($value->expires_at === null ? null : Wire::string($value->expires_at)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
