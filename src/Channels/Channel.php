<?php
declare(strict_types=1);

namespace Automattic\Wistia\Channels;

use Automattic\Wistia\Media\Wire;

/** Typed response record; presentFields distinguishes absent from null. */
final readonly class Channel
{
    public function __construct(
        #[\SensitiveParameter] public int $id,
        #[\SensitiveParameter] public string $created,
        #[\SensitiveParameter] public string $description,
        #[\SensitiveParameter] public string $hashed_id,
        #[\SensitiveParameter] public int $media_count,
        #[\SensitiveParameter] public ?int $live_stream_event_count,
        #[\SensitiveParameter] public string $name,
        #[\SensitiveParameter] public string $updated,
        #[\SensitiveParameter] public ?bool $auto_publish_enabled,
        #[\SensitiveParameter] public ?bool $podcast_enabled,
        #[\SensitiveParameter] public ?string $custom_url,
        #[\SensitiveParameter] public ?ChannelPodcastSettingsRecord $podcast_settings,
        #[\SensitiveParameter] public ?string $cursor,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['id', 'created', 'description', 'hashed_id', 'media_count', 'live_stream_event_count', 'name', 'updated', 'auto_publish_enabled', 'podcast_enabled', 'custom_url', 'podcast_settings', 'cursor'], ['id', 'created', 'description', 'hashed_id', 'media_count', 'name', 'updated']);
        return new self(
            Wire::integer($value->id),
            Wire::string($value->created),
            Wire::string($value->description),
            Wire::string($value->hashed_id),
            Wire::integer($value->media_count),
            property_exists($value, 'live_stream_event_count') ? Wire::integer($value->live_stream_event_count) : null,
            Wire::string($value->name),
            Wire::string($value->updated),
            property_exists($value, 'auto_publish_enabled') ? ($value->auto_publish_enabled === null ? null : Wire::boolean($value->auto_publish_enabled)) : null,
            property_exists($value, 'podcast_enabled') ? ($value->podcast_enabled === null ? null : Wire::boolean($value->podcast_enabled)) : null,
            property_exists($value, 'custom_url') ? ($value->custom_url === null ? null : Wire::string($value->custom_url)) : null,
            property_exists($value, 'podcast_settings') ? ChannelPodcastSettingsRecord::fromWire($value->podcast_settings) : null,
            property_exists($value, 'cursor') ? ($value->cursor === null ? null : Wire::string($value->cursor)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
