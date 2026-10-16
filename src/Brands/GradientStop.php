<?php
declare(strict_types=1);
namespace Automattic\Wistia\Brands;

/** A documented [hexColor, percentage] pair; no undocumented position range is imposed. */
final readonly class GradientStop implements \JsonSerializable
{
    public function __construct(#[\SensitiveParameter] public string $color, public float $position)
    {
        Color::validate($color);
        if (!is_finite($position)) { throw new \InvalidArgumentException('Gradient positions must be finite.'); }
    }
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        if (!is_array($value) || !array_is_list($value) || count($value) !== 2) {
            throw new \UnexpectedValueException('Expected a color/position pair.');
        }
        return new self(\Automattic\Wistia\Media\Wire::string($value[0]), \Automattic\Wistia\Media\Wire::number($value[1]));
    }
    public function jsonSerialize(): array { return [$this->color, $this->position]; }
    public function __debugInfo(): array { return []; }
}
