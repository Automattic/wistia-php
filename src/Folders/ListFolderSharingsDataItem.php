<?php
declare(strict_types=1);

namespace Automattic\Wistia\Folders;

use Automattic\Wistia\Media\Wire;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class ListFolderSharingsDataItem
{
    public function __construct(
        #[\SensitiveParameter] public int $id,
        #[\SensitiveParameter] public bool $is_admin,
        #[\SensitiveParameter] public bool $can_share,
        #[\SensitiveParameter] public bool $can_download,
        #[\SensitiveParameter] public bool $can_upload,
        #[\SensitiveParameter] public ListFolderSharingsDataItemShare $share,
        #[\SensitiveParameter] public ListFolderSharingsDataItemFolder $folder,
        #[\SensitiveParameter] public ?string $cursor,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['id', 'is_admin', 'can_share', 'can_download', 'can_upload', 'share', 'folder', 'cursor'], ['id', 'is_admin', 'can_share', 'can_download', 'can_upload', 'share', 'folder']);
        return new self(
            Wire::integer($value->id),
            Wire::boolean($value->is_admin),
            Wire::boolean($value->can_share),
            Wire::boolean($value->can_download),
            Wire::boolean($value->can_upload),
            ListFolderSharingsDataItemShare::fromWire($value->share),
            ListFolderSharingsDataItemFolder::fromWire($value->folder),
            property_exists($value, 'cursor') ? ($value->cursor === null ? null : Wire::string($value->cursor)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
