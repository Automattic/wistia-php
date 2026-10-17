<?php
declare(strict_types=1);

namespace Automattic\Wistia\Stats;

use Automattic\Wistia\Media\Wire;

final readonly class Thumbnail
{
    public function __construct(
        #[\SensitiveParameter] public string|null $url,
        #[\SensitiveParameter] public int|null $width,
        #[\SensitiveParameter] public int|null $height,
        #[\SensitiveParameter] public int|null $fileSize,
        #[\SensitiveParameter] public string|null $contentType,
        #[\SensitiveParameter] public string|null $type,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['url', 'width', 'height', 'fileSize', 'contentType', 'type'], []);
        return new self(
            property_exists($value, 'url') ? (Wire::string($value->url)) : null,
            property_exists($value, 'width') ? (Wire::integer($value->width)) : null,
            property_exists($value, 'height') ? (Wire::integer($value->height)) : null,
            property_exists($value, 'fileSize') ? (Wire::integer($value->fileSize)) : null,
            property_exists($value, 'contentType') ? (Wire::string($value->contentType)) : null,
            property_exists($value, 'type') ? (Wire::string($value->type)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
