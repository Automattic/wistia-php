<?php
declare(strict_types=1);

namespace Automattic\Wistia\Analytics;

use Automattic\Wistia\Media\Wire;

final readonly class RankedProject
{
    public function __construct(
        #[\SensitiveParameter] public int|null $plays,
        #[\SensitiveParameter] public float|null $play_rate,
        #[\SensitiveParameter] public float|null $engagement_rate,
        #[\SensitiveParameter] public int|null $unique_visitors,
        #[\SensitiveParameter] public float|null $played_time,
        #[\SensitiveParameter] public string|null $project_name,
        #[\SensitiveParameter] public string|null $project_hashed_id,
        #[\SensitiveParameter] public string|null $project_created_at,
        #[\SensitiveParameter] public string|null $project_url,
        #[\SensitiveParameter] public int|null $project_video_count,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['plays','play_rate','engagement_rate','unique_visitors','played_time','project_name','project_hashed_id','project_created_at','project_url','project_video_count'], ['project_hashed_id']);
        return new self(
            property_exists($value, 'plays') && $value->plays !== null ? Wire::integer($value->plays) : null,
            property_exists($value, 'play_rate') && $value->play_rate !== null ? Wire::number($value->play_rate) : null,
            property_exists($value, 'engagement_rate') && $value->engagement_rate !== null ? Wire::number($value->engagement_rate) : null,
            property_exists($value, 'unique_visitors') && $value->unique_visitors !== null ? Wire::integer($value->unique_visitors) : null,
            property_exists($value, 'played_time') && $value->played_time !== null ? Wire::number($value->played_time) : null,
            property_exists($value, 'project_name') && $value->project_name !== null ? Wire::string($value->project_name) : null,
            property_exists($value, 'project_hashed_id') && $value->project_hashed_id !== null ? Wire::string($value->project_hashed_id) : null,
            property_exists($value, 'project_created_at') && $value->project_created_at !== null ? Wire::string($value->project_created_at) : null,
            property_exists($value, 'project_url') && $value->project_url !== null ? Wire::string($value->project_url) : null,
            property_exists($value, 'project_video_count') && $value->project_video_count !== null ? Wire::integer($value->project_video_count) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
