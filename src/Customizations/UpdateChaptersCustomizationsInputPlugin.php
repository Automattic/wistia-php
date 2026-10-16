<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateChaptersCustomizationsInputPlugin implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public UpdateChaptersCustomizationsInputPluginChapters|null|Omitted $chapters = Omitted::Value,
        #[\SensitiveParameter] public UpdateChaptersCustomizationsInputPluginAudioChapters|null|Omitted $audioChapters = Omitted::Value,
    ) {
        if ($this->chapters !== Omitted::Value && $this->chapters !== null) { Shape::check(Shape::encode($this->chapters), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'visibleOnLoad' => ['type' => 'boolean'], 'chapterList' => ['type' => 'array', 'items' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['id' => ['type' => 'string'], 'title' => ['type' => 'string'], 'time' => ['type' => 'string'], 'deleted' => ['type' => 'string']]]]]], true); }
        if ($this->audioChapters !== Omitted::Value && $this->audioChapters !== null) { Shape::check(Shape::encode($this->audioChapters), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'visibleOnLoad' => ['type' => 'boolean'], 'chapterList' => ['type' => 'array', 'items' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['id' => ['type' => 'string'], 'title' => ['type' => 'string'], 'time' => ['type' => 'string'], 'deleted' => ['type' => 'string']]]]]], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->chapters !== Omitted::Value) { $out->{'chapters'} = Shape::encode($this->chapters); }
        if ($this->audioChapters !== Omitted::Value) { $out->{'audioChapters'} = Shape::encode($this->audioChapters); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
