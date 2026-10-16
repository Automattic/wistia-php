<?php
declare(strict_types=1);

namespace Automattic\Wistia\Accessibility;

use Automattic\Wistia\Media\Wire;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class EditCaptionsTextDataEditsItem
{
    public function __construct(
        #[\SensitiveParameter] public string $target_text,
        #[\SensitiveParameter] public string $replacement_text,
        #[\SensitiveParameter] public string $matched_text,
        #[\SensitiveParameter] public string $context,
        #[\SensitiveParameter] public EditCaptionsTextDataEditsItemOriginal $original,
        #[\SensitiveParameter] public ?EditCaptionsTextDataEditsItemReplacement $replacement,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['target_text', 'replacement_text', 'matched_text', 'context', 'original', 'replacement'], ['target_text', 'replacement_text', 'matched_text', 'context', 'original', 'replacement']);
        return new self(
            Wire::string($value->target_text),
            Wire::string($value->replacement_text),
            Wire::string($value->matched_text),
            Wire::string($value->context),
            EditCaptionsTextDataEditsItemOriginal::fromWire($value->original),
            ($value->replacement === null ? null : EditCaptionsTextDataEditsItemReplacement::fromWire($value->replacement)),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
