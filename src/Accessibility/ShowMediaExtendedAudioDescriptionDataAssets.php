<?php
declare(strict_types=1);

namespace Automattic\Wistia\Accessibility;

use Automattic\Wistia\Media\Wire;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class ShowMediaExtendedAudioDescriptionDataAssets
{
    public function __construct(
        #[\SensitiveParameter] public ?string $mp3_url,
        #[\SensitiveParameter] public ?string $webvtt,
        #[\SensitiveParameter] public ?string $webvtt_extended,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['mp3_url', 'webvtt', 'webvtt_extended'], ['mp3_url', 'webvtt', 'webvtt_extended']);
        return new self(
            ($value->mp3_url === null ? null : Wire::string($value->mp3_url)),
            ($value->webvtt === null ? null : Wire::string($value->webvtt)),
            ($value->webvtt_extended === null ? null : Wire::string($value->webvtt_extended)),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
