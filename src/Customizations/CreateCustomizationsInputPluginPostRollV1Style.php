<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class CreateCustomizationsInputPluginPostRollV1Style implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public string|null|Omitted $backgroundColor = Omitted::Value,
    ) {
        if ($this->backgroundColor !== Omitted::Value) { Shape::check(Shape::encode($this->backgroundColor), ['type' => 'string'], false); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->backgroundColor !== Omitted::Value) { $out->{'backgroundColor'} = Shape::encode($this->backgroundColor); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
