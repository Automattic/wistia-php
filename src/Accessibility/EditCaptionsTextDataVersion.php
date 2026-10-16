<?php
declare(strict_types=1);

namespace Automattic\Wistia\Accessibility;

use Automattic\Wistia\Media\Wire;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class EditCaptionsTextDataVersion
{
    public function __construct(
        #[\SensitiveParameter] public int $from,
        #[\SensitiveParameter] public int $to,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['from', 'to'], ['from', 'to']);
        return new self(
            Wire::integer($value->from),
            Wire::integer($value->to),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
