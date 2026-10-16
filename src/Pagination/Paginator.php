<?php
declare(strict_types=1);

namespace Automattic\Wistia\Pagination;

/** Fetch callbacks perform one endpoint read and return an already decoded Page. No prefetch or retries. */
final readonly class Paginator
{
    private \Closure $fetch;
    public function __construct(private PaginationStrategy $strategy, #[\SensitiveParameter] callable $fetch, private int $maxPages = 1000)
    {
        if ($maxPages < 1) { throw new \InvalidArgumentException('A positive finite page bound is required.'); }
        $this->fetch = \Closure::fromCallable($fetch);
    }
    public function page(#[\SensitiveParameter] int|string|null $position = null): Page
    {
        return ($this->fetch)($this->strategy->query($position ?? $this->strategy->first()));
    }
    /** @return \Generator<int, Page> */
    public function pages(): \Generator
    {
        $position = $this->strategy->first(); $seen = [];
        if (is_string($position)) { $seen[$position] = true; }
        for ($count = 1; ; ++$count) {
            $page = $this->page($position);
            yield $page;
            $next = $this->strategy->next($page, $position);
            if ($next === null) { return; }
            if (is_string($next) && isset($seen[$next])) { throw new \UnexpectedValueException('Pagination cursor did not advance.'); }
            if ($count === $this->maxPages) { throw new \OverflowException('Pagination page bound exhausted.'); }
            if (is_string($next)) { $seen[$next] = true; }
            $position = $next;
        }
    }
    public function items(): \Generator { foreach ($this->pages() as $page) { foreach ($page->items as $item) { yield $item; } } }
    public function __debugInfo(): array { return []; }
}
