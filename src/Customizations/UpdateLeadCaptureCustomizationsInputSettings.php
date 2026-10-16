<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateLeadCaptureCustomizationsInputSettings implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public string|null|Omitted $time = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $allowSkip = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $hashedId = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $displayMode = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $showLogo = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $backgroundColor = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $formId = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $portalId = Omitted::Value,
    ) {
        if ($this->time !== Omitted::Value) { Shape::check(Shape::encode($this->time), ['type' => 'string'], false); }
        if ($this->allowSkip !== Omitted::Value) { Shape::check(Shape::encode($this->allowSkip), ['type' => 'boolean'], false); }
        if ($this->hashedId !== Omitted::Value) { Shape::check(Shape::encode($this->hashedId), ['type' => 'string'], false); }
        if ($this->displayMode !== Omitted::Value) { Shape::check(Shape::encode($this->displayMode), ['type' => 'string'], false); }
        if ($this->showLogo !== Omitted::Value) { Shape::check(Shape::encode($this->showLogo), ['type' => 'boolean'], false); }
        if ($this->backgroundColor !== Omitted::Value) { Shape::check(Shape::encode($this->backgroundColor), ['type' => 'string'], false); }
        if ($this->formId !== Omitted::Value) { Shape::check(Shape::encode($this->formId), ['type' => 'string'], false); }
        if ($this->portalId !== Omitted::Value) { Shape::check(Shape::encode($this->portalId), ['type' => 'string'], false); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->time !== Omitted::Value) { $out->{'time'} = Shape::encode($this->time); }
        if ($this->allowSkip !== Omitted::Value) { $out->{'allowSkip'} = Shape::encode($this->allowSkip); }
        if ($this->hashedId !== Omitted::Value) { $out->{'hashedId'} = Shape::encode($this->hashedId); }
        if ($this->displayMode !== Omitted::Value) { $out->{'displayMode'} = Shape::encode($this->displayMode); }
        if ($this->showLogo !== Omitted::Value) { $out->{'showLogo'} = Shape::encode($this->showLogo); }
        if ($this->backgroundColor !== Omitted::Value) { $out->{'backgroundColor'} = Shape::encode($this->backgroundColor); }
        if ($this->formId !== Omitted::Value) { $out->{'formId'} = Shape::encode($this->formId); }
        if ($this->portalId !== Omitted::Value) { $out->{'portalId'} = Shape::encode($this->portalId); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
