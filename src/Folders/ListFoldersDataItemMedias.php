<?php
declare(strict_types=1);

namespace Automattic\Wistia\Folders;

use Automattic\Wistia\Media\Wire;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class ListFoldersDataItemMedias
{
    public function __construct(
        #[\SensitiveParameter] public ?string $url,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['url'], []);
        return new self(
            property_exists($value, 'url') ? Wire::string($value->url) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
