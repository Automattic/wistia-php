<?php
declare(strict_types=1);

namespace Automattic\Wistia\Sharing;

use Automattic\Wistia\Media\Wire;

final readonly class SearchEnvelopeDataMediasItemThumbnail
{
    public function __construct(
        #[\SensitiveParameter] public string|null $url,
        #[\SensitiveParameter] public int|null $width,
        #[\SensitiveParameter] public int|null $height,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['url', 'width', 'height'], []);
        return new self(
            property_exists($value, 'url') ? Wire::string($value->url) : null,
            property_exists($value, 'width') ? Wire::integer($value->width) : null,
            property_exists($value, 'height') ? Wire::integer($value->height) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
