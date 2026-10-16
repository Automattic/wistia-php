<?php
declare(strict_types=1);
namespace Automattic\Wistia\Brands;

final readonly class LogoDimensionsInput implements \JsonSerializable
{
    public function __construct(public ?int $width = null, public ?int $height = null) {}
    public function jsonSerialize(): object { return (object) array_filter(get_object_vars($this), static fn($v) => $v !== null); }
    public function __debugInfo(): array { return []; }
}
