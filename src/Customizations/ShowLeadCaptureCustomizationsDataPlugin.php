<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowLeadCaptureCustomizationsDataPlugin
{
    public function __construct(
        #[\SensitiveParameter] public ShowLeadCaptureCustomizationsDataPluginRequireEmailV1|null $requireEmail_v1,
        #[\SensitiveParameter] public ShowLeadCaptureCustomizationsDataPluginForm|null $form,
        #[\SensitiveParameter] public ShowLeadCaptureCustomizationsDataPluginHubspotForm|null $hubspotForm,
        #[\SensitiveParameter] public ShowLeadCaptureCustomizationsDataPluginMarketoForm|null $marketoForm,
        #[\SensitiveParameter] public ShowLeadCaptureCustomizationsDataPluginPardotForm|null $pardotForm,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['requireEmail-v1' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string']]], 'form' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string']]], 'hubspotForm' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string']]], 'marketoForm' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string']]], 'pardotForm' => ['unevaluatedProperties' => true, 'type' => 'object', 'properties' => ['on' => ['type' => 'string']]]]]);
        return new self(
            property_exists($value, 'requireEmail-v1') ? ShowLeadCaptureCustomizationsDataPluginRequireEmailV1::fromWire($value->{'requireEmail-v1'}) : null,
            property_exists($value, 'form') ? ShowLeadCaptureCustomizationsDataPluginForm::fromWire($value->{'form'}) : null,
            property_exists($value, 'hubspotForm') ? ShowLeadCaptureCustomizationsDataPluginHubspotForm::fromWire($value->{'hubspotForm'}) : null,
            property_exists($value, 'marketoForm') ? ShowLeadCaptureCustomizationsDataPluginMarketoForm::fromWire($value->{'marketoForm'}) : null,
            property_exists($value, 'pardotForm') ? ShowLeadCaptureCustomizationsDataPluginPardotForm::fromWire($value->{'pardotForm'}) : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['requireEmail-v1', 'form', 'hubspotForm', 'marketoForm', 'pardotForm'])),
        );
    }
    public function __debugInfo(): array { return []; }
}
