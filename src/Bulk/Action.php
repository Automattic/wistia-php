<?php
declare(strict_types=1);

namespace Automattic\Wistia\Bulk;

use Automattic\Wistia\Media\Input;

final readonly class Action implements \JsonSerializable
{
    public function __construct(#[\SensitiveParameter] public Change $change,
        #[\SensitiveParameter] public ?string $id = null)
    {
        if ($change->operation === Operation::Create) {
            if ($id !== null) { throw new \InvalidArgumentException('Create actions must not include an ID.'); }
        } else {
            if ($id === null) { throw new \InvalidArgumentException('An action ID is required.'); }
            Input::nonempty($id);
        }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = $this->change->jsonSerialize();
        if ($this->id !== null) { $out->id = $this->id; }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
