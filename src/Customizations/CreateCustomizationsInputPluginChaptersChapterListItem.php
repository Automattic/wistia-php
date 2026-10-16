<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class CreateCustomizationsInputPluginChaptersChapterListItem implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public string|null|Omitted $id = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $title = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $time = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $deleted = Omitted::Value,
    ) {
        if ($this->id !== Omitted::Value) { Shape::check(Shape::encode($this->id), ['type' => 'string'], false); }
        if ($this->title !== Omitted::Value) { Shape::check(Shape::encode($this->title), ['type' => 'string'], false); }
        if ($this->time !== Omitted::Value) { Shape::check(Shape::encode($this->time), ['type' => 'string'], false); }
        if ($this->deleted !== Omitted::Value) { Shape::check(Shape::encode($this->deleted), ['type' => 'string'], false); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->id !== Omitted::Value) { $out->{'id'} = Shape::encode($this->id); }
        if ($this->title !== Omitted::Value) { $out->{'title'} = Shape::encode($this->title); }
        if ($this->time !== Omitted::Value) { $out->{'time'} = Shape::encode($this->time); }
        if ($this->deleted !== Omitted::Value) { $out->{'deleted'} = Shape::encode($this->deleted); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
