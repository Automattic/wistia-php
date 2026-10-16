<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class UpdateChannelEpisodeRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $channelEpisodeHashedId,
        #[\SensitiveParameter] public string|null|\Automattic\Wistia\Channels\Omitted $description = \Automattic\Wistia\Channels\Omitted::Value,
        #[\SensitiveParameter] public string|null|\Automattic\Wistia\Channels\Omitted $title = \Automattic\Wistia\Channels\Omitted::Value,
        #[\SensitiveParameter] public string|null $media_hashed_id = null,
        #[\SensitiveParameter] public string|null $live_stream_event_hashed_id = null,
        #[\SensitiveParameter] public string|null|\Automattic\Wistia\Channels\Omitted $summary = \Automattic\Wistia\Channels\Omitted::Value,
        #[\SensitiveParameter] public string|null $publish_status = null,
        #[\SensitiveParameter] public string|null $publish_at = null,
        #[\SensitiveParameter] public string|null $episode_notes = null,
        #[\SensitiveParameter] public \Automattic\Wistia\Channels\EpisodePodcastSettings|null $podcast_settings = null,
    ) {
        Path::segment((string) $channelEpisodeHashedId); Input::nonempty($channelEpisodeHashedId);
        if ($publish_status !== null) { Input::oneOf($publish_status, ['draft', 'published', 'scheduled']); }
        if ($publish_at !== null) { \Automattic\Wistia\Channels\Scheduling::timestamp($publish_at); }
    }
    private function pairs(): array
    {
        $pairs = [];

        return $pairs;
    }
    public function query(): Query { return new Query($this->pairs()); }
    public function body(): ?Body
    {
        $body = new \stdClass();
        if (!$this->description instanceof \Automattic\Wistia\Channels\Omitted) { $body->description = $this->description; }
        if (!$this->title instanceof \Automattic\Wistia\Channels\Omitted) { $body->title = $this->title; }
        if ($this->media_hashed_id !== null) { $body->media_hashed_id = $this->media_hashed_id; }
        if ($this->live_stream_event_hashed_id !== null) { $body->live_stream_event_hashed_id = $this->live_stream_event_hashed_id; }
        if (!$this->summary instanceof \Automattic\Wistia\Channels\Omitted) { $body->summary = $this->summary; }
        if ($this->publish_status !== null) { $body->publish_status = $this->publish_status; }
        if ($this->publish_at !== null) { $body->publish_at = $this->publish_at; }
        if ($this->episode_notes !== null) { $body->episode_notes = $this->episode_notes; }
        if ($this->podcast_settings !== null) { $body->podcast_settings = $this->podcast_settings; }
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
