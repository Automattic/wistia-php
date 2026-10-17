<?php
declare(strict_types=1);

namespace Automattic\Wistia\Bulk;

use Automattic\Wistia\Media\Input;

final readonly class Scope implements \JsonSerializable
{
    public function __construct(public ScopeType $type, #[\SensitiveParameter] public ?string $id = null)
    {
        if ($type === ScopeType::Account) {
            if ($id !== null) { throw new \InvalidArgumentException('Account scope must not include an ID.'); }
        } else {
            if ($id === null) { throw new \InvalidArgumentException('A parent ID is required.'); }
            Input::nonempty($id);
        }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = (object) ['type' => $this->type->value];
        if ($this->id !== null) { $out->id = $this->id; }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
