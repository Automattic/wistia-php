<?php
declare(strict_types=1);

namespace Automattic\Wistia\Media;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class UpdateMediaData
{
    public function __construct(
        /** @var list<ListMediaDataItemTagsItem>|null */
        #[\SensitiveParameter] public ?array $tags,
        #[\SensitiveParameter] public ?int $id,
        #[\SensitiveParameter] public ?string $name,
        #[\SensitiveParameter] public ?string $type,
        #[\SensitiveParameter] public ?bool $archived,
        #[\SensitiveParameter] public ?string $created,
        #[\SensitiveParameter] public ?string $updated,
        #[\SensitiveParameter] public ?float $duration,
        #[\SensitiveParameter] public ?string $embedCode,
        #[\SensitiveParameter] public ?string $hashed_id,
        #[\SensitiveParameter] public ?string $description,
        #[\SensitiveParameter] public ?float $progress,
        #[\SensitiveParameter] public ?string $status,
        #[\SensitiveParameter] public ?string $section,
        #[\SensitiveParameter] public ?ListMediaDataItemThumbnail $thumbnail,
        #[\SensitiveParameter] public ?bool $protected,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['tags', 'id', 'name', 'type', 'archived', 'created', 'updated', 'duration', 'embedCode', 'hashed_id', 'description', 'progress', 'status', 'section', 'thumbnail', 'protected'], []);
        return new self(
            property_exists($value, 'tags') ? Wire::items($value->tags, static fn($v) => ListMediaDataItemTagsItem::fromWire($v)) : null,
            property_exists($value, 'id') ? Wire::integer($value->id) : null,
            property_exists($value, 'name') ? Wire::string($value->name) : null,
            property_exists($value, 'type') ? Wire::oneOf(Wire::string($value->type), ['Video', 'Audio', 'Image', 'PdfDocument', 'MicrosoftOfficeDocument', 'Swf', 'UnknownType']) : null,
            property_exists($value, 'archived') ? Wire::boolean($value->archived) : null,
            property_exists($value, 'created') ? Wire::string($value->created) : null,
            property_exists($value, 'updated') ? Wire::string($value->updated) : null,
            property_exists($value, 'duration') ? ($value->duration === null ? null : Wire::number($value->duration)) : null,
            property_exists($value, 'embedCode') ? Wire::string($value->embedCode) : null,
            property_exists($value, 'hashed_id') ? Wire::string($value->hashed_id) : null,
            property_exists($value, 'description') ? Wire::string($value->description) : null,
            property_exists($value, 'progress') ? Wire::range(Wire::number($value->progress), 0, 1) : null,
            property_exists($value, 'status') ? Wire::oneOf(Wire::string($value->status), ['queued', 'processing', 'ready', 'failed']) : null,
            property_exists($value, 'section') ? ($value->section === null ? null : Wire::string($value->section)) : null,
            property_exists($value, 'thumbnail') ? ListMediaDataItemThumbnail::fromWire($value->thumbnail) : null,
            property_exists($value, 'protected') ? ($value->protected === null ? null : Wire::boolean($value->protected)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
