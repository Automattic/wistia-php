<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class CreateChannelRequest
{
    public function __construct(
        #[\SensitiveParameter] public string|null|\Automattic\Wistia\Channels\Omitted $name = \Automattic\Wistia\Channels\Omitted::Value,
        #[\SensitiveParameter] public string|null|\Automattic\Wistia\Channels\Omitted $description = \Automattic\Wistia\Channels\Omitted::Value,
        #[\SensitiveParameter] public bool|null $auto_publish_enabled = null,
        #[\SensitiveParameter] public bool|null $podcast_enabled = null,
        #[\SensitiveParameter] public string|null|\Automattic\Wistia\Channels\Omitted $custom_url = \Automattic\Wistia\Channels\Omitted::Value,
        #[\SensitiveParameter] public \Automattic\Wistia\Channels\ChannelPodcastSettings|null $podcast_settings = null,
    ) {
        if ($auto_publish_enabled === true && $podcast_enabled === true) { throw new \InvalidArgumentException('Auto-publishing cannot be enabled with podcasting.'); }
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
        if (!$this->name instanceof \Automattic\Wistia\Channels\Omitted) { $body->name = $this->name; }
        if (!$this->description instanceof \Automattic\Wistia\Channels\Omitted) { $body->description = $this->description; }
        if ($this->auto_publish_enabled !== null) { $body->auto_publish_enabled = $this->auto_publish_enabled; }
        if ($this->podcast_enabled !== null) { $body->podcast_enabled = $this->podcast_enabled; }
        if (!$this->custom_url instanceof \Automattic\Wistia\Channels\Omitted) { $body->custom_url = $this->custom_url; }
        if ($this->podcast_settings !== null) { $body->podcast_settings = $this->podcast_settings; }
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
