<?php
declare(strict_types=1);

namespace Automattic\Wistia\Account;

use Automattic\Wistia\Media\Wire;

final readonly class CurrentTokenApplication
{
    public function __construct(
        #[\SensitiveParameter] public string $name,
        #[\SensitiveParameter] public array $scopes,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['name', 'scopes'], ['name', 'scopes']);
        return new self(
            Wire::string($value->name),
            Wire::items($value->scopes, Wire::string(...)),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
