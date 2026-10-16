<?php
declare(strict_types=1);

namespace Automattic\Wistia\Http;

use Automattic\Wistia\Encoding\Body;
use Automattic\Wistia\Encoding\Query;

/** Shared service-internal contract, not an endpoint service or arbitrary-host tunnel. */
final readonly class Request
{
    /** @param non-empty-list<int> $successStatuses */
    public function __construct(
        public ApiFamily $family,
        public HttpMethod $method,
        #[\SensitiveParameter] public string $path,
        public RequestSafety $safety,
        public array $successStatuses,
        #[\SensitiveParameter] public Query $query = new Query(),
        #[\SensitiveParameter] public ?Body $body = null,
    ) {
        // Components must be encoded individually; never permit host changes or traversal.
        if (!str_starts_with($path, '/') || str_starts_with($path, '//')
            || str_contains($path, '\\') || preg_match('/[?#\x00-\x20\x7f]/', $path)) {
            throw new \InvalidArgumentException('Use an absolute API path with encoded components, without query or fragment.');
        }
        foreach (explode('/', rawurldecode($path)) as $segment) {
            if ($segment === '.' || $segment === '..') {
                throw new \InvalidArgumentException('Path traversal is not allowed.');
            }
        }
        if ($successStatuses === [] || !array_is_list($successStatuses)) {
            throw new \InvalidArgumentException('Declare the endpoint success statuses.');
        }
        foreach ($successStatuses as $status) {
            if (!is_int($status) || $status < 200 || $status > 299) {
                throw new \InvalidArgumentException('Success statuses must be explicit 2xx integers.');
            }
        }
    }

    public function mayChangeRemoteState(): bool
    {
        return $this->method !== HttpMethod::Get || $this->safety === RequestSafety::MayChangeRemoteState;
    }

    public function __debugInfo(): array { return ['family' => $this->family, 'method' => $this->method]; }
}
