<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateCustomizationsInputPluginChapters implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public bool|null|Omitted $visibleOnLoad = Omitted::Value,
        /** @var list<UpdateCustomizationsInputPluginChaptersChapterListItem>|null|Omitted */
        #[\SensitiveParameter] public array|null|Omitted $chapterList = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $on = Omitted::Value,
    ) {
        if ($this->visibleOnLoad !== Omitted::Value && $this->visibleOnLoad !== null) { Shape::check(Shape::encode($this->visibleOnLoad), ['type' => 'boolean'], true); }
        if ($this->chapterList !== Omitted::Value && $this->chapterList !== null) { Shape::check(Shape::encode($this->chapterList), ['type' => 'array', 'items' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['id' => ['type' => 'string'], 'title' => ['type' => 'string'], 'time' => ['type' => 'string'], 'deleted' => ['type' => 'string']]]], true); }
        if ($this->on !== Omitted::Value && $this->on !== null) { Shape::check(Shape::encode($this->on), ['type' => 'boolean'], true); }
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
