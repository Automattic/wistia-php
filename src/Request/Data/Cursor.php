<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

final readonly class Cursor
{
    public function __construct(public ?int $enabled = null,
        #[\SensitiveParameter] public ?string $before = null,
        #[\SensitiveParameter] public ?string $after = null)
    {
        if (($enabled !== null && !in_array($enabled, [0, 1], true)) || ($before !== null && $after !== null)
            || $before === '' || $after === '') {
            throw new \InvalidArgumentException('Use enabled 0/1 and at most one nonempty cursor direction.');
        }
    }
    public function active(): bool { return $this->enabled === 1 || $this->before !== null || $this->after !== null; }
    public function pairs(): array
    {
        $pairs = [];
        foreach (['enabled', 'before', 'after'] as $key) {
            if ($this->$key !== null) { $pairs[] = ['cursor[' . $key . ']', $this->$key]; }
        }
        return $pairs;
    }
    public function __debugInfo(): array { return []; }
}
