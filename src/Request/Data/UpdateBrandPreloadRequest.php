<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;
use Automattic\Wistia\Brands\BrandAttributes;

final readonly class UpdateBrandPreloadRequest
{
    public function __construct(
        #[\SensitiveParameter] public ?string $selected_player_color = null,
        #[\SensitiveParameter] public ?string $selected_logo_hashed_id = null,
    ) {
        if ($selected_player_color !== null && preg_match('/\A(#?[0-9a-fA-F]{6})?\z/D', $selected_player_color) !== 1) {
            throw new \InvalidArgumentException('Player color must be six hex digits, optionally prefixed with #, or empty.');
        }
    }
    public function query(): Query
    {
        $pairs = [];

        return new Query($pairs);
    }
    public function body(): ?Body
    {
        $out = new \stdClass();
        if ($this->selected_player_color !== null) { $out->selected_player_color = $this->selected_player_color; }
        if ($this->selected_logo_hashed_id !== null) { $out->selected_logo_hashed_id = $this->selected_logo_hashed_id; }
        return Body::json($out);
    }
    public function __debugInfo(): array { return []; }
}
