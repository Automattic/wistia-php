<?php
declare(strict_types=1);

namespace Automattic\Wistia\Webinars;

use Automattic\Wistia\Media\Wire;

/** Typed response record; presentFields distinguishes absent fields from null. */
final readonly class WebinarRegistration
{
    public function __construct(
        #[\SensitiveParameter] public ?string $visitor_key,
        #[\SensitiveParameter] public ?string $email,
        #[\SensitiveParameter] public ?string $first_name,
        #[\SensitiveParameter] public ?string $last_name,
        #[\SensitiveParameter] public ?string $phone_number,
        #[\SensitiveParameter] public ?string $company,
        #[\SensitiveParameter] public ?string $job_title,
        #[\SensitiveParameter] public ?string $country,
        #[\SensitiveParameter] public ?string $registered_at,
        #[\SensitiveParameter] public ?string $attended_at,
        #[\SensitiveParameter] public ?string $restriction_status,
        #[\SensitiveParameter] public ?string $origin_method,
        /** @var list<RegistrationFormField>|null */
        #[\SensitiveParameter] public ?array $form_field_data,
        #[\SensitiveParameter] public ?string $utm_campaign,
        #[\SensitiveParameter] public ?string $utm_source,
        #[\SensitiveParameter] public ?string $utm_medium,
        #[\SensitiveParameter] public ?string $utm_content,
        #[\SensitiveParameter] public ?string $utm_term,
        #[\SensitiveParameter] public ?string $referrer_domain,
        #[\SensitiveParameter] public ?string $ip_region,
        #[\SensitiveParameter] public ?string $ip_country,
        #[\SensitiveParameter] public ?string $ip_city,
        #[\SensitiveParameter] public ?float $engagement_rate,
        #[\SensitiveParameter] public ?float $focused_rate,
        #[\SensitiveParameter] public ?int $played_time,
        #[\SensitiveParameter] public ?int $focused_played_time,
        #[\SensitiveParameter] public ?int $chat_count,
        /** @var list<RegistrationChatGroup>|null */
        #[\SensitiveParameter] public ?array $chats,
        /** @var list<RegistrationQuestionGroup>|null */
        #[\SensitiveParameter] public ?array $qa_questions,
        #[\SensitiveParameter] public ?int $qa_answers,
        /** @var list<RegistrationPollResponse>|null */
        #[\SensitiveParameter] public ?array $poll_responses,
        #[\SensitiveParameter] public ?int $poll_responses_count,
        #[\SensitiveParameter] public ?int $annotation_click_count,
        /** @var list<int | null>|null */
        #[\SensitiveParameter] public ?array $attendee_histogram,
        /** @var list<int | null>|null */
        #[\SensitiveParameter] public ?array $focused_attendee_histogram,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['visitor_key', 'email', 'first_name', 'last_name', 'phone_number', 'company', 'job_title', 'country', 'registered_at', 'attended_at', 'restriction_status', 'origin_method', 'form_field_data', 'utm_campaign', 'utm_source', 'utm_medium', 'utm_content', 'utm_term', 'referrer_domain', 'ip_region', 'ip_country', 'ip_city', 'engagement_rate', 'focused_rate', 'played_time', 'focused_played_time', 'chat_count', 'chats', 'qa_questions', 'qa_answers', 'poll_responses', 'poll_responses_count', 'annotation_click_count', 'attendee_histogram', 'focused_attendee_histogram'], []);
        return new self(
            property_exists($value, 'visitor_key') ? ($value->visitor_key === null ? null : Wire::string($value->visitor_key)) : null,
            property_exists($value, 'email') ? ($value->email === null ? null : Wire::string($value->email)) : null,
            property_exists($value, 'first_name') ? ($value->first_name === null ? null : Wire::string($value->first_name)) : null,
            property_exists($value, 'last_name') ? ($value->last_name === null ? null : Wire::string($value->last_name)) : null,
            property_exists($value, 'phone_number') ? ($value->phone_number === null ? null : Wire::string($value->phone_number)) : null,
            property_exists($value, 'company') ? ($value->company === null ? null : Wire::string($value->company)) : null,
            property_exists($value, 'job_title') ? ($value->job_title === null ? null : Wire::string($value->job_title)) : null,
            property_exists($value, 'country') ? ($value->country === null ? null : Wire::string($value->country)) : null,
            property_exists($value, 'registered_at') ? ($value->registered_at === null ? null : Wire::string($value->registered_at)) : null,
            property_exists($value, 'attended_at') ? ($value->attended_at === null ? null : Wire::string($value->attended_at)) : null,
            property_exists($value, 'restriction_status') ? ($value->restriction_status === null ? null : Wire::oneOf(Wire::string($value->restriction_status), ['restricted', 'allowed', null])) : null,
            property_exists($value, 'origin_method') ? ($value->origin_method === null ? null : Wire::string($value->origin_method)) : null,
            property_exists($value, 'form_field_data') ? Wire::items($value->form_field_data, static fn($item) => RegistrationFormField::fromWire($item)) : null,
            property_exists($value, 'utm_campaign') ? ($value->utm_campaign === null ? null : Wire::string($value->utm_campaign)) : null,
            property_exists($value, 'utm_source') ? ($value->utm_source === null ? null : Wire::string($value->utm_source)) : null,
            property_exists($value, 'utm_medium') ? ($value->utm_medium === null ? null : Wire::string($value->utm_medium)) : null,
            property_exists($value, 'utm_content') ? ($value->utm_content === null ? null : Wire::string($value->utm_content)) : null,
            property_exists($value, 'utm_term') ? ($value->utm_term === null ? null : Wire::string($value->utm_term)) : null,
            property_exists($value, 'referrer_domain') ? ($value->referrer_domain === null ? null : Wire::string($value->referrer_domain)) : null,
            property_exists($value, 'ip_region') ? ($value->ip_region === null ? null : Wire::string($value->ip_region)) : null,
            property_exists($value, 'ip_country') ? ($value->ip_country === null ? null : Wire::string($value->ip_country)) : null,
            property_exists($value, 'ip_city') ? ($value->ip_city === null ? null : Wire::string($value->ip_city)) : null,
            property_exists($value, 'engagement_rate') ? ($value->engagement_rate === null ? null : Wire::number($value->engagement_rate)) : null,
            property_exists($value, 'focused_rate') ? ($value->focused_rate === null ? null : Wire::number($value->focused_rate)) : null,
            property_exists($value, 'played_time') ? ($value->played_time === null ? null : Wire::integer($value->played_time)) : null,
            property_exists($value, 'focused_played_time') ? ($value->focused_played_time === null ? null : Wire::integer($value->focused_played_time)) : null,
            property_exists($value, 'chat_count') ? ($value->chat_count === null ? null : Wire::integer($value->chat_count)) : null,
            property_exists($value, 'chats') ? Wire::items($value->chats, static fn($item) => RegistrationChatGroup::fromWire($item)) : null,
            property_exists($value, 'qa_questions') ? Wire::items($value->qa_questions, static fn($item) => RegistrationQuestionGroup::fromWire($item)) : null,
            property_exists($value, 'qa_answers') ? ($value->qa_answers === null ? null : Wire::integer($value->qa_answers)) : null,
            property_exists($value, 'poll_responses') ? Wire::items($value->poll_responses, static fn($item) => RegistrationPollResponse::fromWire($item)) : null,
            property_exists($value, 'poll_responses_count') ? ($value->poll_responses_count === null ? null : Wire::integer($value->poll_responses_count)) : null,
            property_exists($value, 'annotation_click_count') ? ($value->annotation_click_count === null ? null : Wire::integer($value->annotation_click_count)) : null,
            property_exists($value, 'attendee_histogram') ? ($value->attendee_histogram === null ? null : Wire::items($value->attendee_histogram, static fn($item) => ($item === null ? null : Wire::integer($item)))) : null,
            property_exists($value, 'focused_attendee_histogram') ? ($value->focused_attendee_histogram === null ? null : Wire::items($value->focused_attendee_histogram, static fn($item) => ($item === null ? null : Wire::integer($item)))) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
