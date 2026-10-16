<?php
declare(strict_types=1);

namespace Automattic\Wistia\Accessibility;

use Automattic\Wistia\Media\Wire;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class EditCaptionsTextDataEditsItemOriginal
{
    public function __construct(
        #[\SensitiveParameter] public int $start_ms,
        #[\SensitiveParameter] public int $end_ms,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['start_ms', 'end_ms'], ['start_ms', 'end_ms']);
        return new self(
            Wire::integer($value->start_ms),
            Wire::integer($value->end_ms),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
