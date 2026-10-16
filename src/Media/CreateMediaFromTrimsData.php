<?php
declare(strict_types=1);

namespace Automattic\Wistia\Media;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class CreateMediaFromTrimsData
{
    public function __construct(
        #[\SensitiveParameter] public ?SwapMediaDataBackgroundJobStatus $background_job_status,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['background_job_status'], []);
        return new self(
            property_exists($value, 'background_job_status') ? SwapMediaDataBackgroundJobStatus::fromWire($value->background_job_status) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
