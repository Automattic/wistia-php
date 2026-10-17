<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Analytics;

use Automattic\Wistia\Analytics\WebinarAudienceMember;
use Automattic\Wistia\Analytics\MediaPageInfo;
use Automattic\Wistia\Analytics\RecordList;
use Automattic\Wistia\Pagination\Page;
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class ShowWebinarAudienceResult implements Result
{
    public function __construct(
        /** @var list<WebinarAudienceMember>|null */ #[\SensitiveParameter] public ?array $data,
        #[\SensitiveParameter] public ?MediaPageInfo $page_info,
        public int $status,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        Wire::object($value, ['data', 'page_info'], []);
        return new self(
            property_exists($value, 'data') ? RecordList::decode($value->data, WebinarAudienceMember::fromWire(...)) : null,
            property_exists($value, 'page_info') ? MediaPageInfo::fromWire($value->page_info) : null,
            $status, array_keys(get_object_vars($value)));
    }
    /** @return Page<WebinarAudienceMember> */
    public function page(): Page
    {
        // Forward traversal requires an explicit continuation flag, not reverse metadata.
        // The pagination strategy rejects a missing/empty end cursor only when continuing.
        if ($this->data === null || $this->page_info === null || $this->page_info->has_next_page === null) {
            throw new \UnexpectedValueException('Incomplete pagination metadata.');
        }
        return new Page($this->data, $this->page_info->end_cursor, $this->page_info->has_next_page,
            $this->page_info->start_cursor, $this->page_info->has_previous_page ?? false);
    }
    public function __debugInfo(): array { return []; }
}
