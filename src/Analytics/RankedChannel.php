<?php
declare(strict_types=1);

namespace Automattic\Wistia\Analytics;

use Automattic\Wistia\Media\Wire;

final readonly class RankedChannel
{
    public function __construct(
        #[\SensitiveParameter] public int|null $plays,
        #[\SensitiveParameter] public float|null $play_rate,
        #[\SensitiveParameter] public float|null $engagement_rate,
        #[\SensitiveParameter] public int|null $unique_visitors,
        #[\SensitiveParameter] public float|null $played_time,
        #[\SensitiveParameter] public string|null $channel_title,
        #[\SensitiveParameter] public string|null $channel_hashed_id,
        #[\SensitiveParameter] public int|null $channel_episode_count,
        #[\SensitiveParameter] public string|null $channel_created_at,
        #[\SensitiveParameter] public string|null $channel_url,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['plays','play_rate','engagement_rate','unique_visitors','played_time','channel_title','channel_hashed_id','channel_episode_count','channel_created_at','channel_url'], ['channel_hashed_id']);
        return new self(
            property_exists($value, 'plays') && $value->plays !== null ? Wire::integer($value->plays) : null,
            property_exists($value, 'play_rate') && $value->play_rate !== null ? Wire::number($value->play_rate) : null,
            property_exists($value, 'engagement_rate') && $value->engagement_rate !== null ? Wire::number($value->engagement_rate) : null,
            property_exists($value, 'unique_visitors') && $value->unique_visitors !== null ? Wire::integer($value->unique_visitors) : null,
            property_exists($value, 'played_time') && $value->played_time !== null ? Wire::number($value->played_time) : null,
            property_exists($value, 'channel_title') && $value->channel_title !== null ? Wire::string($value->channel_title) : null,
            property_exists($value, 'channel_hashed_id') && $value->channel_hashed_id !== null ? Wire::string($value->channel_hashed_id) : null,
            property_exists($value, 'channel_episode_count') && $value->channel_episode_count !== null ? Wire::integer($value->channel_episode_count) : null,
            property_exists($value, 'channel_created_at') && $value->channel_created_at !== null ? Wire::string($value->channel_created_at) : null,
            property_exists($value, 'channel_url') && $value->channel_url !== null ? Wire::string($value->channel_url) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
