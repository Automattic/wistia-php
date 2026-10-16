<?php
declare(strict_types=1);

namespace Automattic\Wistia\Channels;

use Automattic\Wistia\Media\Input;

final readonly class EpisodePodcastSettings implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public string|null|Omitted $episode_type = Omitted::Value,
        #[\SensitiveParameter] public int|null|Omitted $episode_number = Omitted::Value,
        #[\SensitiveParameter] public int|null|Omitted $season_number = Omitted::Value,
        #[\SensitiveParameter] public bool|null $explicit_content = null,
        #[\SensitiveParameter] public bool|null $hide_from_feed = null,
    ) {
        if ($episode_type !== null && !$episode_type instanceof Omitted) { Input::oneOf($episode_type, ['full', 'trailer', 'bonus']); }
    }
    public function jsonSerialize(): object
    {
        $values = get_object_vars($this);
        return (object) array_filter($values, static fn($v, $k) => !$v instanceof Omitted && (!in_array($k, ['explicit_content', 'hide_from_feed'], true) || $v !== null), ARRAY_FILTER_USE_BOTH);
    }
    public function __debugInfo(): array { return []; }
}
