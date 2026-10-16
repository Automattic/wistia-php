<?php
declare(strict_types=1);

namespace Automattic\Wistia\Accessibility;

use Automattic\Wistia\Media\Wire;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class EditCaptionsTextData
{
    public function __construct(
        #[\SensitiveParameter] public bool $applied,
        #[\SensitiveParameter] public string $media_id,
        #[\SensitiveParameter] public string $language,
        #[\SensitiveParameter] public EditCaptionsTextDataVersion $version,
        /** @var list<EditCaptionsTextDataEditsItem>|null */
        #[\SensitiveParameter] public array $edits,
        /** @var list<string>|null */
        #[\SensitiveParameter] public array $warnings,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['applied', 'media_id', 'language', 'version', 'edits', 'warnings'], ['applied', 'media_id', 'language', 'version', 'edits', 'warnings']);
        return new self(
            Wire::boolean($value->applied),
            Wire::string($value->media_id),
            Wire::string($value->language),
            EditCaptionsTextDataVersion::fromWire($value->version),
            Wire::items($value->edits, static fn($v) => EditCaptionsTextDataEditsItem::fromWire($v)),
            Wire::items($value->warnings, static fn($v) => Wire::string($v)),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
