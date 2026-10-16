<?php
declare(strict_types=1);

namespace Automattic\Wistia\Media;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class MoveMediaDataPartialErrorsItem
{
    public function __construct(
        #[\SensitiveParameter] public ?string $media_id,
        #[\SensitiveParameter] public ?string $error,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['media_id', 'error'], []);
        return new self(
            property_exists($value, 'media_id') ? Wire::string($value->media_id) : null,
            property_exists($value, 'error') ? Wire::string($value->error) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
