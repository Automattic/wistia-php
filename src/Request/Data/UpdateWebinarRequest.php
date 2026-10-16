<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class UpdateWebinarRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $id,
        #[\SensitiveParameter] public \Automattic\Wistia\Webinars\WebinarUpdate|null $webinar = null,
    ) {
        Path::segment((string) $id); Input::nonempty($id);
    }
    private function pairs(): array
    {
        $pairs = [];

        return $pairs;
    }
    public function query(): Query { return new Query($this->pairs()); }
    public function body(): ?Body
    {
        $body = new \stdClass();
        if ($this->webinar !== null) { $body->webinar = $this->webinar; }
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
