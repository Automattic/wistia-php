<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateChaptersCustomizationsInputPluginChapters implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public bool|null|Omitted $on = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $visibleOnLoad = Omitted::Value,
        /** @var list<UpdateChaptersCustomizationsInputPluginChaptersChapterListItem>|null|Omitted */
        #[\SensitiveParameter] public array|null|Omitted $chapterList = Omitted::Value,
    ) {
        if ($this->on !== Omitted::Value && $this->on !== null) { Shape::check(Shape::encode($this->on), ['type' => 'boolean'], true); }
        if ($this->visibleOnLoad !== Omitted::Value && $this->visibleOnLoad !== null) { Shape::check(Shape::encode($this->visibleOnLoad), ['type' => 'boolean'], true); }
        if ($this->chapterList !== Omitted::Value && $this->chapterList !== null) { Shape::check(Shape::encode($this->chapterList), ['type' => 'array', 'items' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['id' => ['type' => 'string'], 'title' => ['type' => 'string'], 'time' => ['type' => 'string'], 'deleted' => ['type' => 'string']]]], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->on !== Omitted::Value) { $out->{'on'} = Shape::encode($this->on); }
        if ($this->visibleOnLoad !== Omitted::Value) { $out->{'visibleOnLoad'} = Shape::encode($this->visibleOnLoad); }
        if ($this->chapterList !== Omitted::Value) { $out->{'chapterList'} = Shape::encode($this->chapterList); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
