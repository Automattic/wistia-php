<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class CreateWebinarRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $title,
        #[\SensitiveParameter] public string $scheduled_for,
        #[\SensitiveParameter] public int $event_duration,
        #[\SensitiveParameter] public string $time_zone,
        #[\SensitiveParameter] public string|null $description = null,
        #[\SensitiveParameter] public string|null $folder_id = null,
    ) {
        \Automattic\Wistia\Webinars\Validation::scheduled_for($scheduled_for);
        \Automattic\Wistia\Webinars\Validation::event_duration($event_duration);
        \Automattic\Wistia\Webinars\Validation::time_zone($time_zone);
        if ($folder_id !== null) { Input::nonempty($folder_id); }
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
        { $body->title = $this->title; }
        { $body->scheduled_for = $this->scheduled_for; }
        { $body->event_duration = $this->event_duration; }
        { $body->time_zone = $this->time_zone; }
        if ($this->description !== null) { $body->description = $this->description; }
        if ($this->folder_id !== null) { $body->folder_id = $this->folder_id; }
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
