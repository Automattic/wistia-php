<?php
declare(strict_types=1);

namespace Automattic\Wistia\Account;

use Automattic\Wistia\Media\Wire;

final readonly class InvitedContacts
{
    public function __construct(
        #[\SensitiveParameter] public array $contacts,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['contacts'], ['contacts']);
        return new self(
            Wire::items($value->contacts, InvitedContactsContactsItem::fromWire(...)),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
