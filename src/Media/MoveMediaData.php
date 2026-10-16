<?php
declare(strict_types=1);

namespace Automattic\Wistia\Media;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class MoveMediaData
{
    public function __construct(
        #[\SensitiveParameter] public string $message,
        #[\SensitiveParameter] public SwapMediaDataBackgroundJobStatus $background_job_status,
        /** @var list<MoveMediaDataPartialErrorsItem>|null */
        #[\SensitiveParameter] public ?array $partial_errors,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['message', 'background_job_status', 'partial_errors'], ['message', 'background_job_status']);
        return new self(
            Wire::string($value->message),
            SwapMediaDataBackgroundJobStatus::fromWire($value->background_job_status),
            property_exists($value, 'partial_errors') ? Wire::items($value->partial_errors, static fn($v) => MoveMediaDataPartialErrorsItem::fromWire($v)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
