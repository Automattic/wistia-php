<?php
declare(strict_types=1);

namespace Automattic\Wistia\Account;

use Automattic\Wistia\Media\Wire;

final readonly class CurrentAccount
{
    public function __construct(
        #[\SensitiveParameter] public int $id,
        #[\SensitiveParameter] public string $name,
        #[\SensitiveParameter] public string $url,
        #[\SensitiveParameter] public int $media_count,
        #[\SensitiveParameter] public int|null $video_limit,
        #[\SensitiveParameter] public int $folder_count,
        #[\SensitiveParameter] public int $channel_count,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['id', 'name', 'url', 'media_count', 'video_limit', 'folder_count', 'channel_count'], ['id', 'name', 'url', 'media_count', 'video_limit', 'folder_count', 'channel_count']);
        return new self(
            Wire::integer($value->id),
            Wire::string($value->name),
            Wire::string($value->url),
            Wire::integer($value->media_count),
            ($value->video_limit === null ? null : Wire::integer($value->video_limit)),
            Wire::integer($value->folder_count),
            Wire::integer($value->channel_count),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
