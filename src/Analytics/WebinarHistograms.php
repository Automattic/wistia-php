<?php
declare(strict_types=1);

namespace Automattic\Wistia\Analytics;

use Automattic\Wistia\Media\Wire;

final readonly class WebinarHistograms
{
    public function __construct(
        /** @var list<float>|null */ #[\SensitiveParameter] public ?array $attendees_histogram,
        /** @var list<float>|null */ #[\SensitiveParameter] public ?array $chat_histogram,
        /** @var list<float>|null */ #[\SensitiveParameter] public ?array $visual_focus_played_histogram,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['attendees_histogram','chat_histogram','visual_focus_played_histogram'], []);
        return new self(
            property_exists($value, 'attendees_histogram') ? RecordList::decode($value->attendees_histogram, static fn (#[\SensitiveParameter] mixed $item) => Wire::number($item)) : null,
            property_exists($value, 'chat_histogram') ? RecordList::decode($value->chat_histogram, static fn (#[\SensitiveParameter] mixed $item) => Wire::number($item)) : null,
            property_exists($value, 'visual_focus_played_histogram') ? RecordList::decode($value->visual_focus_played_histogram, static fn (#[\SensitiveParameter] mixed $item) => Wire::number($item)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
