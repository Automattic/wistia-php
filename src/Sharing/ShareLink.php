<?php
declare(strict_types=1);

namespace Automattic\Wistia\Sharing;

use Automattic\Wistia\Media\Wire;

final readonly class ShareLink
{
    public function __construct(
        #[\SensitiveParameter] public string $id,
        #[\SensitiveParameter] public string $media_id,
        #[\SensitiveParameter] public string $url,
        #[\SensitiveParameter] public string|null $slug,
        #[\SensitiveParameter] public string $visibility,
        #[\SensitiveParameter] public string $created_at,
        #[\SensitiveParameter] public string $updated_at,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['id', 'media_id', 'url', 'slug', 'visibility', 'created_at', 'updated_at'], ['id', 'media_id', 'url', 'slug', 'visibility', 'created_at', 'updated_at']);
        return new self(
            Wire::string($value->id),
            Wire::string($value->media_id),
            Wire::string($value->url),
            ($value->slug === null ? null : Wire::string($value->slug)),
            Wire::oneOf(Wire::string($value->visibility), ['unlocked', 'account', 'locked', 'domain_verified']),
            Wire::string($value->created_at),
            Wire::string($value->updated_at),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
