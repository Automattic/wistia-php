<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class FindCaptionMatchesRequest
{
    public function __construct(
        /** @var list<string> */
        #[\SensitiveParameter] public array $media_ids,
        #[\SensitiveParameter] public string $target_text,
        #[\SensitiveParameter] public ?string $language_code = null,
        #[\SensitiveParameter] public ?int $occurrence = null,
        #[\SensitiveParameter] public ?int $start_ms = null,
        #[\SensitiveParameter] public ?int $end_ms = null,
    ) {
        Input::strings($media_ids); Input::count($media_ids, 1, 50); if (count(array_unique($media_ids)) !== count($media_ids)) { throw new \InvalidArgumentException('Duplicate identifiers are not allowed.'); }
        Input::nonempty($target_text); Input::nonempty($target_text); if (preg_match_all('/./us', $target_text) > 500 || preg_match('//u', $target_text) !== 1) { throw new \InvalidArgumentException('Text exceeds the documented limit or is not UTF-8.'); }
        if ($language_code !== null) { Input::nonempty($language_code); }
        if ($occurrence !== null) { if ($occurrence < 1) { throw new \InvalidArgumentException('Pagination and occurrence values must be positive.'); } }
        if ($start_ms !== null) { if ($start_ms < 0) { throw new \InvalidArgumentException('Value is below the documented minimum.'); } }
        if ($end_ms !== null) { if ($end_ms < 0) { throw new \InvalidArgumentException('Value is below the documented minimum.'); } }
        // Search documents independent optional bounds; pairing is required only for edits.
        if ($start_ms !== null && $end_ms !== null && $end_ms < $start_ms) {
            throw new \InvalidArgumentException('The time range must end at or after it starts.');
        }
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
        $body->media_ids = $this->media_ids;
        $body->target_text = $this->target_text;
        if ($this->language_code !== null) { $body->language_code = $this->language_code; }
        if ($this->occurrence !== null) { $body->occurrence = $this->occurrence; }
        if ($this->start_ms !== null) { $body->start_ms = $this->start_ms; }
        if ($this->end_ms !== null) { $body->end_ms = $this->end_ms; }
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
