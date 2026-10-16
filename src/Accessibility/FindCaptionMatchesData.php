<?php
declare(strict_types=1);

namespace Automattic\Wistia\Accessibility;

use Automattic\Wistia\Media\Wire;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class FindCaptionMatchesData
{
    public function __construct(
        #[\SensitiveParameter] public int $requested_count,
        #[\SensitiveParameter] public int $succeeded_count,
        #[\SensitiveParameter] public int $failed_count,
        #[\SensitiveParameter] public bool $complete,
        /** @var list<FindCaptionMatchesDataResultsItem>|null */
        #[\SensitiveParameter] public array $results,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['requested_count', 'succeeded_count', 'failed_count', 'complete', 'results'], ['requested_count', 'succeeded_count', 'failed_count', 'complete', 'results']);
        return new self(
            Wire::integer($value->requested_count),
            Wire::integer($value->succeeded_count),
            Wire::integer($value->failed_count),
            Wire::boolean($value->complete),
            Wire::items($value->results, static fn($v) => FindCaptionMatchesDataResultsItem::fromWire($v)),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
