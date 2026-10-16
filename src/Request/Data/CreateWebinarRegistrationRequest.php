<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class CreateWebinarRegistrationRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $webinarId,
        #[\SensitiveParameter] public string $email,
        #[\SensitiveParameter] public string $first_name,
        #[\SensitiveParameter] public string $last_name,
    ) {
        Path::segment((string) $webinarId); Input::nonempty($webinarId);
        \Automattic\Wistia\Webinars\Validation::email($email);
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
        { $body->email = $this->email; }
        { $body->first_name = $this->first_name; }
        { $body->last_name = $this->last_name; }
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
