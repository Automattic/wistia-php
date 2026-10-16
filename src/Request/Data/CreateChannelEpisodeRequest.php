<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class CreateChannelEpisodeRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $channelHashedId,
        #[\SensitiveParameter] public string|null $media_id = null,
        #[\SensitiveParameter] public string|null $title = null,
        #[\SensitiveParameter] public string|null $description = null,
        #[\SensitiveParameter] public string|null $summary = null,
        #[\SensitiveParameter] public string|null $publish_status = null,
        #[\SensitiveParameter] public string|null $publish_at = null,
        #[\SensitiveParameter] public \Automattic\Wistia\Channels\EpisodePodcastSettings|null $podcast_settings = null,
    ) {
        Path::segment((string) $channelHashedId); Input::nonempty($channelHashedId);
        if ($publish_status !== null) { Input::oneOf($publish_status, ['draft', 'published', 'scheduled']); }
        if ($publish_at !== null) { \Automattic\Wistia\Channels\Scheduling::timestamp($publish_at); }
        \Automattic\Wistia\Channels\Scheduling::creation($publish_status, $publish_at);
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
        if ($this->media_id !== null) { $body->media_id = $this->media_id; }
        if ($this->title !== null) { $body->title = $this->title; }
        if ($this->description !== null) { $body->description = $this->description; }
        if ($this->summary !== null) { $body->summary = $this->summary; }
        if ($this->publish_status !== null) { $body->publish_status = $this->publish_status; }
        if ($this->publish_at !== null) { $body->publish_at = $this->publish_at; }
        if ($this->podcast_settings !== null) { $body->podcast_settings = $this->podcast_settings; }
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
