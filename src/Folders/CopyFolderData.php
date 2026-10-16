<?php
declare(strict_types=1);

namespace Automattic\Wistia\Folders;

use Automattic\Wistia\Media\Wire;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class CopyFolderData
{
    public function __construct(
        #[\SensitiveParameter] public int $id,
        #[\SensitiveParameter] public string $hashed_id,
        #[\SensitiveParameter] public string $status,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['id', 'hashed_id', 'status'], ['id', 'hashed_id', 'status']);
        return new self(
            Wire::integer($value->id),
            Wire::string($value->hashed_id),
            Wire::oneOf(Wire::string($value->status), ['queued', 'started', 'finished', 'failed']),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
