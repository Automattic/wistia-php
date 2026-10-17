<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Encoding\{Query, Body};
use Automattic\Wistia\Media\Input;
use Automattic\Wistia\Sharing\{MetadataFilter, Validation};

final readonly class SearchRequest
{
    /** @param list<string>|null $tags
     * @param list<string>|null $resource_type
     * @param array<string, MetadataFilter>|null $custom_metadata
     */
    public function __construct(
        #[\SensitiveParameter] public string $q,
        #[\SensitiveParameter] public ?array $tags = null,
        #[\SensitiveParameter] public ?array $resource_type = null,
        #[\SensitiveParameter] public ?array $custom_metadata = null,
        #[\SensitiveParameter] public ?string $include = null,
        #[\SensitiveParameter] public ?string $created_after = null,
        #[\SensitiveParameter] public ?string $created_before = null,
    ) {
        // Empty q is explicitly supported for custom metadata audits.
        if ($tags !== null) { Input::strings($tags); }
        if ($resource_type !== null) {
            Input::strings($resource_type);
            foreach ($resource_type as $type) { Input::oneOf($type, ['media', 'folder', 'subfolder', 'channel', 'channel_episode', 'webinar']); }
        }
        if ($custom_metadata !== null) {
            if ($resource_type === null || !in_array('media', $resource_type, true)) {
                throw new \InvalidArgumentException('Custom metadata filtering requires resource_type to include media.');
            }
            foreach ($custom_metadata as $field => $filter) {
                if (!is_string($field) || trim($field) === '' || strpbrk($field, '[]') !== false || !$filter instanceof MetadataFilter) {
                    throw new \InvalidArgumentException('Supply field keys mapped to MetadataFilter values.');
                }
            }
        }
        if ($include !== null) { Input::oneOf($include, ['custom_metadata']); }
        foreach ([$created_after, $created_before] as $date) { if ($date !== null) { Validation::utc($date); } }
    }
    public function query(): Query
    {
        $pairs = [['q', $this->q]];
        foreach (['tags' => $this->tags, 'resource_type' => $this->resource_type] as $key => $values) {
            foreach ($values ?? [] as $value) { $pairs[] = [$key . '[]', $value]; }
        }
        foreach ($this->custom_metadata ?? [] as $field => $filter) { $pairs = [...$pairs, ...$filter->pairs($field)]; }
        foreach (['include', 'created_after', 'created_before'] as $key) {
            if ($this->$key !== null) { $pairs[] = [$key, $this->$key]; }
        }
        return new Query($pairs);
    }
    public function body(): ?Body { return null; }
    public function __debugInfo(): array { return []; }
}
