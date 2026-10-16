<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateLeadCaptureCustomizationsInput implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public string $provider,
        #[\SensitiveParameter] public bool|null|Omitted $enabled = Omitted::Value,
        #[\SensitiveParameter] public UpdateLeadCaptureCustomizationsInputSettings|null|Omitted $settings = Omitted::Value,
    ) {
        if ($this->provider !== Omitted::Value) { Shape::check(Shape::encode($this->provider), ['type' => 'string', 'enum' => ['wistia_form', 'hubspot', 'marketo', 'pardot']], false); }
        if ($this->enabled !== Omitted::Value) { Shape::check(Shape::encode($this->enabled), ['type' => 'boolean'], false); }
        if ($this->settings !== Omitted::Value) { Shape::check(Shape::encode($this->settings), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['time' => ['type' => 'string'], 'allowSkip' => ['type' => 'boolean'], 'hashedId' => ['type' => 'string'], 'displayMode' => ['type' => 'string'], 'showLogo' => ['type' => 'boolean'], 'backgroundColor' => ['type' => 'string'], 'formId' => ['type' => 'string'], 'portalId' => ['type' => 'string']]], false); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->provider !== Omitted::Value) { $out->{'provider'} = Shape::encode($this->provider); }
        if ($this->enabled !== Omitted::Value) { $out->{'enabled'} = Shape::encode($this->enabled); }
        if ($this->settings !== Omitted::Value) { $out->{'settings'} = Shape::encode($this->settings); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
