<?php
declare(strict_types=1);

namespace Automattic\Wistia\Pagination;

use Automattic\Wistia\Encoding\Query;

interface PaginationStrategy
{
    public function first(): int|string|null;
    public function query(#[\SensitiveParameter] int|string|null $position): Query;
    public function next(#[\SensitiveParameter] Page $page, #[\SensitiveParameter] int|string|null $position): int|string|null;
}
