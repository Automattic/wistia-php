<?php
declare(strict_types=1);

namespace Automattic\Wistia\Media;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class ListMediaDataItemAssetsItem
{
    public function __construct(
        #[\SensitiveParameter] public ?string $url,
        #[\SensitiveParameter] public ?int $width,
        #[\SensitiveParameter] public ?int $height,
        #[\SensitiveParameter] public ?int $file_size,
        #[\SensitiveParameter] public ?string $content_type,
        #[\SensitiveParameter] public ?string $type,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['url', 'width', 'height', 'file_size', 'content_type', 'type'], []);
        return new self(
            property_exists($value, 'url') ? Wire::string($value->url) : null,
            property_exists($value, 'width') ? ($value->width === null ? null : Wire::integer($value->width)) : null,
            property_exists($value, 'height') ? ($value->height === null ? null : Wire::integer($value->height)) : null,
            property_exists($value, 'file_size') ? ($value->file_size === null ? null : Wire::integer($value->file_size)) : null,
            property_exists($value, 'content_type') ? ($value->content_type === null ? null : Wire::string($value->content_type)) : null,
            property_exists($value, 'type') ? Wire::string($value->type) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
