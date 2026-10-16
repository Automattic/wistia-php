<?php
declare(strict_types=1);

namespace Automattic\Wistia\Media;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class RestoreDeletedMediaData
{
    public function __construct(
        #[\SensitiveParameter] public ?string $message,
        #[\SensitiveParameter] public ?RestoreDeletedMediaDataBackgroundJobStatus $background_job_status,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['message', 'background_job_status'], []);
        return new self(
            property_exists($value, 'message') ? Wire::string($value->message) : null,
            property_exists($value, 'background_job_status') ? RestoreDeletedMediaDataBackgroundJobStatus::fromWire($value->background_job_status) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
