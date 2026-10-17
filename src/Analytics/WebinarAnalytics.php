<?php
declare(strict_types=1);

namespace Automattic\Wistia\Analytics;

use Automattic\Wistia\Media\Wire;

final readonly class WebinarAnalytics
{
    public function __construct(
        #[\SensitiveParameter] public ?int $registrations,
        #[\SensitiveParameter] public ?int $imported_registrations,
        #[\SensitiveParameter] public ?int $on_demand_registrations,
        #[\SensitiveParameter] public ?int $imported_on_demand_registrations,
        #[\SensitiveParameter] public ?int $total_watch_time,
        #[\SensitiveParameter] public ?int $average_watch_time,
        #[\SensitiveParameter] public ?int $attendance,
        #[\SensitiveParameter] public ?int $chatted_attendees,
        #[\SensitiveParameter] public ?float $engagement_rate,
        #[\SensitiveParameter] public ?int $chats,
        #[\SensitiveParameter] public ?int $currently_watching,
        #[\SensitiveParameter] public ?int $engaged_attendees,
        #[\SensitiveParameter] public ?int $impressions,
        #[\SensitiveParameter] public ?int $on_demand_impressions,
        #[\SensitiveParameter] public ?int $on_demand_plays,
        #[\SensitiveParameter] public ?float $on_demand_engagement_rate,
        #[\SensitiveParameter] public ?int $event_time,
        #[\SensitiveParameter] public ?int $qa_questions,
        /** @var list<WebinarPollQuestion>|null */ #[\SensitiveParameter] public ?array $poll_questions,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['registrations','imported_registrations','on_demand_registrations','imported_on_demand_registrations','total_watch_time','average_watch_time','attendance','chatted_attendees','engagement_rate','chats','currently_watching','engaged_attendees','impressions','on_demand_impressions','on_demand_plays','on_demand_engagement_rate','event_time','qa_questions','poll_questions'], []);
        return new self(
            property_exists($value, 'registrations') ? ($value->registrations === null ? null : Wire::integer($value->registrations)) : null,
            property_exists($value, 'imported_registrations') ? ($value->imported_registrations === null ? null : Wire::integer($value->imported_registrations)) : null,
            property_exists($value, 'on_demand_registrations') ? ($value->on_demand_registrations === null ? null : Wire::integer($value->on_demand_registrations)) : null,
            property_exists($value, 'imported_on_demand_registrations') ? ($value->imported_on_demand_registrations === null ? null : Wire::integer($value->imported_on_demand_registrations)) : null,
            property_exists($value, 'total_watch_time') ? ($value->total_watch_time === null ? null : Wire::integer($value->total_watch_time)) : null,
            property_exists($value, 'average_watch_time') ? ($value->average_watch_time === null ? null : Wire::integer($value->average_watch_time)) : null,
            property_exists($value, 'attendance') ? ($value->attendance === null ? null : Wire::integer($value->attendance)) : null,
            property_exists($value, 'chatted_attendees') ? ($value->chatted_attendees === null ? null : Wire::integer($value->chatted_attendees)) : null,
            property_exists($value, 'engagement_rate') ? ($value->engagement_rate === null ? null : Wire::number($value->engagement_rate)) : null,
            property_exists($value, 'chats') ? ($value->chats === null ? null : Wire::integer($value->chats)) : null,
            property_exists($value, 'currently_watching') ? ($value->currently_watching === null ? null : Wire::integer($value->currently_watching)) : null,
            property_exists($value, 'engaged_attendees') ? ($value->engaged_attendees === null ? null : Wire::integer($value->engaged_attendees)) : null,
            property_exists($value, 'impressions') ? ($value->impressions === null ? null : Wire::integer($value->impressions)) : null,
            property_exists($value, 'on_demand_impressions') ? ($value->on_demand_impressions === null ? null : Wire::integer($value->on_demand_impressions)) : null,
            property_exists($value, 'on_demand_plays') ? ($value->on_demand_plays === null ? null : Wire::integer($value->on_demand_plays)) : null,
            property_exists($value, 'on_demand_engagement_rate') ? ($value->on_demand_engagement_rate === null ? null : Wire::number($value->on_demand_engagement_rate)) : null,
            property_exists($value, 'event_time') ? ($value->event_time === null ? null : Wire::integer($value->event_time)) : null,
            property_exists($value, 'qa_questions') ? ($value->qa_questions === null ? null : Wire::integer($value->qa_questions)) : null,
            property_exists($value, 'poll_questions') ? ($value->poll_questions === null ? null : RecordList::decode($value->poll_questions, WebinarPollQuestion::fromWire(...))) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
