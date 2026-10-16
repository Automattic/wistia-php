<?php
declare(strict_types=1);

namespace Automattic\Wistia\Media;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class SwapMediaData
{
    public function __construct(
        #[\SensitiveParameter] public string $message,
        #[\SensitiveParameter] public SwapMediaDataBackgroundJobStatus $background_job_status,
        #[\SensitiveParameter] public ?DeleteMediaData $media,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['message', 'background_job_status', 'media'], ['message', 'background_job_status']);
        return new self(
            Wire::string($value->message),
            SwapMediaDataBackgroundJobStatus::fromWire($value->background_job_status),
            property_exists($value, 'media') ? DeleteMediaData::fromWire($value->media) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
