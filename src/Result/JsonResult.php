<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result;

/** A JSON syntax primitive, not validation of an endpoint's schema. Objects remain objects. */
final readonly class JsonResult implements Result
{
    public function __construct(#[\SensitiveParameter] public mixed $value) {}
    public function __debugInfo(): array { return []; }
}
