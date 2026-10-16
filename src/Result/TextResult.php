<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result;

final readonly class TextResult implements Result
{
    public function __construct(#[\SensitiveParameter] public string $value) {}
    public function __debugInfo(): array { return []; }
}
