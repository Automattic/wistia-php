<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class DeleteWebinarCollaboratorRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $webinarId,
        #[\SensitiveParameter] public int $id,
    ) {
        Path::segment((string) $webinarId); Input::nonempty($webinarId);
        Path::segment((string) $id); if ($id < 1) { throw new \InvalidArgumentException('A positive identifier is required.'); }
    }
    private function pairs(): array
    {
        $pairs = [];

        return $pairs;
    }
    public function query(): Query { return new Query($this->pairs()); }
    public function body(): ?Body
    {
        return null;
    }
    public function __debugInfo(): array { return []; }
}
