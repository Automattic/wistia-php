<?php
declare(strict_types=1);

namespace Automattic\Wistia\Upload;

final readonly class UploadOptions
{
    public function __construct(
        #[\SensitiveParameter] public ?string $project_id = null,
        #[\SensitiveParameter] public ?string $name = null,
        #[\SensitiveParameter] public ?string $description = null,
        public ?int $contact_id = null,
    ) {
        if ($project_id !== null) { Input::id($project_id); }
        // UTF-8 code points, without requiring ext-mbstring. Invalid UTF-8 is rejected locally.
        if ($name !== null && (preg_match('//u', $name) !== 1 || preg_match_all('/./us', $name) > 255)) {
            throw new \InvalidArgumentException('Upload name must contain at most 255 UTF-8 characters.');
        }
        if ($contact_id !== null && $contact_id < 1) { throw new \InvalidArgumentException('Contact ID must be positive.'); }
    }

    /** @internal Null options are omitted; empty strings and zero-like names remain explicit. */
    public function fields(): array
    {
        return array_filter(['project_id' => $this->project_id, 'name' => $this->name,
            'description' => $this->description, 'contact_id' => $this->contact_id], static fn ($v) => $v !== null);
    }
    public function __debugInfo(): array { return []; }
}
