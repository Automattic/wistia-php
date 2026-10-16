<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Path, Query, Body};
use Automattic\Wistia\Media\Input;

final readonly class ShowChannelEpisodeRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $channelHashedId,
        #[\SensitiveParameter] public string $channelEpisodeId,
    ) {
        Path::segment((string) $channelHashedId); Input::nonempty($channelHashedId);
        Path::segment((string) $channelEpisodeId); Input::nonempty($channelEpisodeId);
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
