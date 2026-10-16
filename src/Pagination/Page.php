<?php
declare(strict_types=1);

namespace Automattic\Wistia\Pagination;

/** @template T */
final readonly class Page
{
    /** @param list<T> $items */
    public function __construct(#[\SensitiveParameter] public array $items,
        #[\SensitiveParameter] public ?string $endCursor = null,
        public bool $hasNextPage = false,
        #[\SensitiveParameter] public ?string $startCursor = null,
        public bool $hasPreviousPage = false)
    {
        if (!array_is_list($items)) { throw new \InvalidArgumentException('Page items must be a list.'); }
    }
    public function __debugInfo(): array { return ['count' => count($this->items)]; }
}
