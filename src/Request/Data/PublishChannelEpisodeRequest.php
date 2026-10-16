<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class PublishChannelEpisodeRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $channelEpisodeHashedId,
        #[\SensitiveParameter] public string|null $publish_at = null,
    ) {
        Path::segment((string) $channelEpisodeHashedId); Input::nonempty($channelEpisodeHashedId);
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
        if ($this->publish_at !== null) { $body->publish_at = $this->publish_at; }
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
