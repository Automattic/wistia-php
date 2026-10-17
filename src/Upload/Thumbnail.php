<?php
declare(strict_types=1);

namespace Automattic\Wistia\Upload;

use Automattic\Wistia\Media\Wire;

final readonly class Thumbnail
{
    public function __construct(public ?string $url, public ?int $width, public ?int $height) {}
    public static function fromWire(#[\SensitiveParameter] mixed $v): self
    {
        if (!$v instanceof \stdClass) { throw new \UnexpectedValueException('Expected upload thumbnail object.'); }
        foreach (['url', 'width', 'height'] as $key) {
            if (property_exists($v, $key) && $v->$key === null) { throw new \UnexpectedValueException('Unexpected null thumbnail field.'); }
        }
        return new self(isset($v->url) ? Wire::string($v->url) : null, isset($v->width) ? Wire::integer($v->width) : null, isset($v->height) ? Wire::integer($v->height) : null);
    }
    public function __debugInfo(): array { return []; }
}
