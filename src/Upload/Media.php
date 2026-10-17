<?php
declare(strict_types=1);

namespace Automattic\Wistia\Upload;

use Automattic\Wistia\Media\Wire;

/** Upload media is not a modern Data Media result. Absent optional fields remain null. */
final readonly class Media
{
    public function __construct(
        public ?int $id, public ?string $hashed_id, public ?string $name, public ?string $type,
        public ?string $created, public ?string $updated, public ?float $duration, public ?float $progress,
        public ?Thumbnail $thumbnail,
        // Non-rejecting projections of observed extension fields; raw values stay in additionalFields.
        public ?string $status, public ?bool $archived, public ?string $description, public ?int $account_id,
        public array $presentFields, public array $additionalFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $v): self
    {
        if (!$v instanceof \stdClass) { throw new \UnexpectedValueException('Expected upload media object.'); }
        $data = [];
        $types = ['id' => 'integer', 'hashed_id' => 'string', 'name' => 'string', 'type' => 'string',
            'created' => 'string', 'updated' => 'string', 'duration' => 'number', 'progress' => 'number',
            'thumbnail' => 'thumbnail'];
        foreach ($types as $key => $type) {
            $data[$key] = property_exists($v, $key)
                ? ($type === 'thumbnail' ? Thumbnail::fromWire($v->$key) : Wire::$type($v->$key)) : null;
        }
        if ($data['progress'] !== null) { Wire::range($data['progress'], 0, 1); }
        return new self(...$data,
            status: is_string($v->status ?? null) ? $v->status : null,
            archived: is_bool($v->archived ?? null) ? $v->archived : null,
            description: is_string($v->description ?? null) ? $v->description : null,
            account_id: is_int($v->account_id ?? null) ? $v->account_id : null,
            presentFields: array_keys(get_object_vars($v)),
            additionalFields: array_diff_key(get_object_vars($v), $types));
    }
    public function __debugInfo(): array { return []; }
}
