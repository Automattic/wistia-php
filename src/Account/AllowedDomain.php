<?php
declare(strict_types=1);

namespace Automattic\Wistia\Account;

use Automattic\Wistia\Media\Wire;

final readonly class AllowedDomain
{
    public function __construct(
        #[\SensitiveParameter] public string $domain,
        #[\SensitiveParameter] public string $created_at,
        #[\SensitiveParameter] public string|null $cursor,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['domain', 'created_at', 'cursor'], ['domain', 'created_at']);
        return new self(
            Wire::string($value->domain),
            Wire::string($value->created_at),
            property_exists($value, 'cursor') ? ($value->cursor === null ? null : Wire::string($value->cursor)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
