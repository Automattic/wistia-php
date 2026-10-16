<?php
declare(strict_types=1);

namespace Automattic\Wistia\Account;

use Automattic\Wistia\Media\Wire;

final readonly class DesktopPromptDismissal
{
    public function __construct(
        #[\SensitiveParameter] public string $dismissed_at,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['dismissed_at'], ['dismissed_at']);
        return new self(
            Wire::string($value->dismissed_at),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
