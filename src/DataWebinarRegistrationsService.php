<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};

final readonly class DataWebinarRegistrationsService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function listWebinarRegistrations(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\ListWebinarRegistrationsRequest $request): \Automattic\Wistia\Result\Data\ListWebinarRegistrationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/webinars/' . Path::segment((string) $request->webinarId) . '/registrations', RequestSafety::ReadOnly, [200], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\ListWebinarRegistrationsResult::class))->result;
    }

    /** Lazy traversal preserving filters and ordering, with a finite page bound. */
    public function paginateWebinarRegistrations(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\ListWebinarRegistrationsRequest $request, int $maxPages = 1000): \Automattic\Wistia\Pagination\Paginator
    {
        $path = '/webinars/' . Path::segment((string) $request->webinarId) . '/registrations';
        return new \Automattic\Wistia\Pagination\Paginator($request->paginationStrategy(),
            function (\Automattic\Wistia\Encoding\Query $query) use ($path, $request): \Automattic\Wistia\Pagination\Page {
                $result = $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
                    $path, RequestSafety::ReadOnly, [200], $query),
                    new Media\Decoder(\Automattic\Wistia\Result\Data\ListWebinarRegistrationsResult::class))->result;
                return $result->data->page();
            }, $maxPages);
    }

    public function createWebinarRegistration(#[\SensitiveParameter] \Automattic\Wistia\Request\Data\CreateWebinarRegistrationRequest $request): \Automattic\Wistia\Result\Data\CreateWebinarRegistrationResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/webinars/' . Path::segment((string) $request->webinarId) . '/registrations', RequestSafety::MayChangeRemoteState, [201], $request->query(), $request->body()),
            new Media\Decoder(\Automattic\Wistia\Result\Data\CreateWebinarRegistrationResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}
