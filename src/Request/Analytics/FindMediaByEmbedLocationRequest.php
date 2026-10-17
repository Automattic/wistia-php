<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Analytics;

use Automattic\Wistia\Encoding\Query;
use Automattic\Wistia\Media\Input;
use Automattic\Wistia\Analytics\Validation;

final readonly class FindMediaByEmbedLocationRequest
{
    public function __construct(
        #[\SensitiveParameter] public string $embed_url,
        #[\SensitiveParameter] public ?string $start_date = null,
        #[\SensitiveParameter] public ?string $end_date = null,
        #[\SensitiveParameter] public ?string $path_match = null,
        #[\SensitiveParameter] public ?int $per_page = null,
    ) {
        Validation::range($start_date, $end_date, false);
        Validation::embedUrl($embed_url);
        if ($path_match !== null) { Input::oneOf($path_match, ['exact','prefix']); }
        Validation::perPage($per_page, 1000);
    }
    public function query(): Query
    {
        $pairs = [];
        if ($this->embed_url !== null) { $pairs[] = ['embed_url', $this->embed_url]; }
        if ($this->start_date !== null) { $pairs[] = ['start_date', $this->start_date]; }
        if ($this->end_date !== null) { $pairs[] = ['end_date', $this->end_date]; }
        if ($this->path_match !== null) { $pairs[] = ['path_match', $this->path_match]; }
        if ($this->per_page !== null) { $pairs[] = ['per_page', $this->per_page]; }
        return new Query($pairs);
    }
    public function __debugInfo(): array { return []; }
}
