<?php
declare(strict_types=1);

namespace Automattic\Wistia\Analytics;

use Automattic\Wistia\Media\Wire;

final readonly class WebinarPollQuestion
{
    public function __construct(
        #[\SensitiveParameter] public ?int $id,
        #[\SensitiveParameter] public ?int $poll_id,
        #[\SensitiveParameter] public ?string $text,
        #[\SensitiveParameter] public ?int $response_count,
        /** @var list<WebinarPollOption>|null */ #[\SensitiveParameter] public ?array $options,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['id','poll_id','text','response_count','options'], []);
        return new self(
            property_exists($value, 'id') ? Wire::integer($value->id) : null,
            property_exists($value, 'poll_id') ? Wire::integer($value->poll_id) : null,
            property_exists($value, 'text') ? Wire::string($value->text) : null,
            property_exists($value, 'response_count') ? Wire::integer($value->response_count) : null,
            property_exists($value, 'options') ? ($value->options === null ? null : RecordList::decode($value->options, WebinarPollOption::fromWire(...))) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
