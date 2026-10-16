<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateChaptersCustomizationsInput implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public UpdateChaptersCustomizationsInputPlugin|null|Omitted $plugin = Omitted::Value,
    ) {
        if ($this->plugin !== Omitted::Value && $this->plugin !== null) { Shape::check(Shape::encode($this->plugin), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['chapters' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'visibleOnLoad' => ['type' => 'boolean'], 'chapterList' => ['type' => 'array', 'items' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['id' => ['type' => 'string'], 'title' => ['type' => 'string'], 'time' => ['type' => 'string'], 'deleted' => ['type' => 'string']]]]]], 'audioChapters' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'visibleOnLoad' => ['type' => 'boolean'], 'chapterList' => ['type' => 'array', 'items' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['id' => ['type' => 'string'], 'title' => ['type' => 'string'], 'time' => ['type' => 'string'], 'deleted' => ['type' => 'string']]]]]]]], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->plugin !== Omitted::Value) { $out->{'plugin'} = Shape::encode($this->plugin); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
