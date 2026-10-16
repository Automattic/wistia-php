<?php
declare(strict_types=1);

namespace Automattic\Wistia\Folders;

use Automattic\Wistia\Media\Wire;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class CreateFolderSharingData
{
    public function __construct(
        #[\SensitiveParameter] public ?string $project,
        #[\SensitiveParameter] public ?ListFolderSharingsDataItem $sharing,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['project', 'sharing'], []);
        return new self(
            property_exists($value, 'project') ? Wire::string($value->project) : null,
            property_exists($value, 'sharing') ? ListFolderSharingsDataItem::fromWire($value->sharing) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
