<?php
declare(strict_types=1);
namespace Automattic\Wistia\Accessibility;

use Automattic\Wistia\Media\Input;

final readonly class CaptionEdit
{
    public function __construct(
        #[\SensitiveParameter] public string $target_text,
        #[\SensitiveParameter] public string $replacement_text,
        public ?int $start_ms = null,
        public ?int $end_ms = null,
    ) {
        Input::nonempty($target_text);
        Validation::window($start_ms, $end_ms);
    }
    public function toWire(): \stdClass
    {
        $value = (object) ['target_text' => $this->target_text, 'replacement_text' => $this->replacement_text];
        if ($this->start_ms !== null) { $value->start_ms = $this->start_ms; $value->end_ms = $this->end_ms; }
        return $value;
    }
    public function __debugInfo(): array { return []; }
}
