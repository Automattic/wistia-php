<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Analytics;

use Automattic\Wistia\Encoding\Query;
use Automattic\Wistia\Analytics\Validation;
use Automattic\Wistia\Media\Input;

final readonly class ShowMediaEmbedLocationsTimeseriesRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $mediaId,
        #[\SensitiveParameter] public string $start_date,
        #[\SensitiveParameter] public string $end_date,
        #[\SensitiveParameter] public string $granularity,
        #[\SensitiveParameter] public ?string $sort_by = null,
        #[\SensitiveParameter] public ?string $embed_url = null,
        #[\SensitiveParameter] public ?int $per_page = null,
    ) {
        Input::nonempty($mediaId);
        Validation::range($start_date, $end_date);
        Input::oneOf($granularity, ['daily', 'weekly', 'monthly']);
        if ($sort_by !== null) { Input::oneOf($sort_by, ['plays', 'loads', 'engagement_rate', 'play_rate', 'played_time', 'unique_visitors']); }
        if ($embed_url !== null) { Validation::embedUrl($embed_url); }
        Validation::perPage($per_page, 100);
    }
    public function query(): Query
    {
        $pairs = [];
        $pairs[] = ['start_date', $this->start_date];
        $pairs[] = ['end_date', $this->end_date];
        $pairs[] = ['granularity', $this->granularity];
        if ($this->sort_by !== null) { $pairs[] = ['sort_by', $this->sort_by]; }
        if ($this->embed_url !== null) { $pairs[] = ['embed_url', $this->embed_url]; }
        if ($this->per_page !== null) { $pairs[] = ['per_page', $this->per_page]; }
        return new Query($pairs);
    }
    public function __debugInfo(): array { return []; }
}
