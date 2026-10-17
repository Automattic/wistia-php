<?php
declare(strict_types=1);

namespace Automattic\Wistia\Analytics;

use Automattic\Wistia\Media\Wire;

final readonly class MediaFormConversion
{
    public function __construct(
        #[\SensitiveParameter] public string|null $first_name,
        #[\SensitiveParameter] public string|null $last_name,
        #[\SensitiveParameter] public string|null $email,
        #[\SensitiveParameter] public string|null $converted_at,
        /** @var list<MediaFormField>|null */ #[\SensitiveParameter] public array|null $custom_fields,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['first_name','last_name','email','converted_at','custom_fields'], []);
        return new self(
            property_exists($value, 'first_name') && $value->first_name !== null ? Wire::string($value->first_name) : null,
            property_exists($value, 'last_name') && $value->last_name !== null ? Wire::string($value->last_name) : null,
            property_exists($value, 'email') && $value->email !== null ? Wire::string($value->email) : null,
            property_exists($value, 'converted_at') ? Wire::string($value->converted_at) : null,
            property_exists($value, 'custom_fields') ? RecordList::decode($value->custom_fields, MediaFormField::fromWire(...)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
