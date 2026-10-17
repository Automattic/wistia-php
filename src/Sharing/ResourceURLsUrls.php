<?php
declare(strict_types=1);

namespace Automattic\Wistia\Sharing;

use Automattic\Wistia\Media\Wire;

final readonly class ResourceURLsUrls
{
    public function __construct(
        #[\SensitiveParameter] public string $show,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['show'], ['show']);
        return new self(
            Wire::string($value->show),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
