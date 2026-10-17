<?php
declare(strict_types=1);

namespace Automattic\Wistia\Sharing;

use Automattic\Wistia\Media\Wire;

final readonly class SearchEnvelopeDataMediasItemTranscriptMatchesItem
{
    public function __construct(
        #[\SensitiveParameter] public string $text,
        #[\SensitiveParameter] public float $start_time_seconds,
        #[\SensitiveParameter] public string $start_time_formatted,
        #[\SensitiveParameter] public string|null $thumbnail_url,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['text', 'start_time_seconds', 'start_time_formatted', 'thumbnail_url'], ['text', 'start_time_seconds', 'start_time_formatted', 'thumbnail_url']);
        return new self(
            Wire::string($value->text),
            Wire::number($value->start_time_seconds),
            Wire::string($value->start_time_formatted),
            ($value->thumbnail_url === null ? null : Wire::string($value->thumbnail_url)),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
