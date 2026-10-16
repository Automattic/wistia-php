<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class EditCaptionsTextRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $mediaHashedId,
        #[\SensitiveParameter] public string $languageCode,
        /** @var list<\Automattic\Wistia\Accessibility\CaptionEdit> */
        #[\SensitiveParameter] public array $edits,
        #[\SensitiveParameter] public int $expected_version,
    ) {
        Path::segment($mediaHashedId); Input::nonempty($mediaHashedId);
        Path::segment($languageCode); Input::nonempty($languageCode);
        Input::count($edits, 1, 20);
        if (!array_is_list($edits)) { throw new \InvalidArgumentException('Expected a list of caption edits.'); }
        foreach ($edits as $edit) { if (!$edit instanceof \Automattic\Wistia\Accessibility\CaptionEdit) { throw new \InvalidArgumentException('Expected typed caption edits.'); } }
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
        $body->edits = array_map(static fn($edit) => $edit->toWire(), $this->edits);
        $body->expected_version = $this->expected_version;
        return Body::json($body);
    }
    public function __debugInfo(): array { return []; }
}
