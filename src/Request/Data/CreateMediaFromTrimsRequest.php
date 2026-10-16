<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class CreateMediaFromTrimsRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $mediaHashedId,
        #[\SensitiveParameter] public array $trims,
        #[\SensitiveParameter] public ?bool $keep_trims = null,
    ) {
        Path::segment($mediaHashedId); Input::nonempty($mediaHashedId);
        Input::strings($trims);
        Input::trims($trims);
    }
    public function query(): Query
    {
        $pairs = [];

        return new Query($pairs);
    }
    public function body(): ?Body
    {
        $body = new \stdClass();
        $body->trims = $this->trims;
        if ($this->keep_trims !== null) { $body->keep_trims = $this->keep_trims; }
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
