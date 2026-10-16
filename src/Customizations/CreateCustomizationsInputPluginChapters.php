<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class CreateCustomizationsInputPluginChapters implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public bool|null|Omitted $visibleOnLoad = Omitted::Value,
        /** @var list<CreateCustomizationsInputPluginChaptersChapterListItem>|null|Omitted */
        #[\SensitiveParameter] public array|null|Omitted $chapterList = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $on = Omitted::Value,
    ) {
        if ($this->visibleOnLoad !== Omitted::Value) { Shape::check(Shape::encode($this->visibleOnLoad), ['type' => 'boolean'], false); }
        if ($this->chapterList !== Omitted::Value) { Shape::check(Shape::encode($this->chapterList), ['type' => 'array', 'items' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['id' => ['type' => 'string'], 'title' => ['type' => 'string'], 'time' => ['type' => 'string'], 'deleted' => ['type' => 'string']]]], false); }
        if ($this->on !== Omitted::Value) { Shape::check(Shape::encode($this->on), ['type' => 'boolean'], false); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->visibleOnLoad !== Omitted::Value) { $out->{'visibleOnLoad'} = Shape::encode($this->visibleOnLoad); }
        if ($this->chapterList !== Omitted::Value) { $out->{'chapterList'} = Shape::encode($this->chapterList); }
        if ($this->on !== Omitted::Value) { $out->{'on'} = Shape::encode($this->on); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
