<?php
declare(strict_types=1);

namespace Automattic\Wistia\Webinars;

/** Null means omitted; the endpoint documents no explicit-null resets. */
final readonly class WebinarUpdate implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public ?string $title = null,
        #[\SensitiveParameter] public ?string $description = null,
        #[\SensitiveParameter] public ?string $scheduled_for = null,
        #[\SensitiveParameter] public ?int $event_duration = null,
        #[\SensitiveParameter] public ?string $time_zone = null,
        #[\SensitiveParameter] public ?string $folder_id = null,
    ) {
        if ($scheduled_for !== null) { Validation::scheduled_for($scheduled_for); }
        if ($event_duration !== null) { Validation::event_duration($event_duration); }
        if ($time_zone !== null) { Validation::time_zone($time_zone); }
        if ($folder_id !== null) { \Automattic\Wistia\Media\Input::nonempty($folder_id); }
    }
    public function jsonSerialize(): object { return (object) array_filter(get_object_vars($this), static fn($v) => $v !== null); }
    public function __debugInfo(): array { return []; }
}
