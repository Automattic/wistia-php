<?php
declare(strict_types=1);
namespace Automattic\Wistia\Brands;

final readonly class Gradient implements \JsonSerializable
{
    /** @param list<GradientStop> $stops */
    public function __construct(#[\SensitiveParameter] public array $stops)
    {
        if (!array_is_list($stops)) { throw new \InvalidArgumentException('Expected a list of gradient stops.'); }
        foreach ($stops as $stop) {
            if (!$stop instanceof GradientStop) { throw new \InvalidArgumentException('Expected typed gradient stops.'); }
        }
    }
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        return new self(\Automattic\Wistia\Media\Wire::items($value, GradientStop::fromWire(...)));
    }
    public function jsonSerialize(): array { return $this->stops; }
    public function __debugInfo(): array { return []; }
}
