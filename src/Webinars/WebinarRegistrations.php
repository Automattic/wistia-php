<?php
declare(strict_types=1);

namespace Automattic\Wistia\Webinars;

use Automattic\Wistia\Media\Wire;

/** Typed response record; presentFields distinguishes absent fields from null. */
final readonly class WebinarRegistrations
{
    public function __construct(
        /** @var list<WebinarRegistration>|null */
        #[\SensitiveParameter] public ?array $registrations,
        #[\SensitiveParameter] public ?RegistrationPageInfo $page_info,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['registrations', 'page_info'], []);
        return new self(
            property_exists($value, 'registrations') ? Wire::items($value->registrations, static fn($item) => WebinarRegistration::fromWire($item)) : null,
            property_exists($value, 'page_info') ? RegistrationPageInfo::fromWire($value->page_info) : null,
            array_keys(get_object_vars($value)),
        );
    }
    /** Map typed results to the shared Data page-info adapter without changing wire semantics. */
    public function page(): \Automattic\Wistia\Pagination\Page
    {
        return \Automattic\Wistia\Pagination\DataCursorPagination::fromWebinarRegistrations((object) [
            'registrations' => $this->registrations,
            'page_info' => $this->page_info === null ? null : (object) array_intersect_key(
                get_object_vars($this->page_info), array_flip($this->page_info->presentFields)),
        ]);
    }
    public function __debugInfo(): array { return []; }
}
