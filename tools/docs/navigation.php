<?php

declare(strict_types=1);

/** Public reading order and publish allowlist. Keys are paths below docs/site, without .md. */
function docsNavigation(): array
{
    return [
        'Getting started' => [
            'index' => 'Overview',
            'tutorials/first-api-call' => 'Your first API call',
        ],
        'How-to guides' => [
            'Read and write safely' => [
                'how-to/pagination' => 'Read more than one page',
                'how-to/jobs' => 'Follow a background job',
                'how-to/errors' => 'Handle failures',
                'how-to/oauth' => 'Connect with OAuth',
                'how-to/webhooks' => 'Handle webhooks',
            ],
            'Manage content' => [
                'how-to/media' => 'Media',
                'how-to/uploads' => 'Uploads and imports',
                'how-to/customizations' => 'Player customizations',
                'how-to/accessibility' => 'Captions and accessibility',
                'how-to/folders' => 'Folders and sharing',
                'how-to/bulk' => 'Bulk changes',
            ],
            'Account and publishing' => [
                'how-to/channels' => 'Channels and episodes',
                'how-to/webinars' => 'Webinars and registrations',
                'how-to/brands' => 'Branding',
                'how-to/account' => 'Account and access',
                'how-to/search-sharing' => 'Search and review links',
                'how-to/reporting' => 'Stats and Analytics reports',
            ],
        ],
        'Reference' => [
            '@api' => 'PHP API reference',
            'reference/services' => 'Services and operations',
            'reference/configuration' => 'Configuration',
            'reference/errors' => 'Exceptions and rate limits',
        ],
        'Concepts' => [
            'concepts/client-model' => 'Services, requests and results',
            'concepts/updates' => 'Omission, null and replacement',
            'concepts/async-work' => 'Asynchronous work',
        ],
        'Framework integration' => [
            'integrations/plain-php' => 'Plain PHP examples',
            'integrations/laravel' => 'Laravel',
            'integrations/symfony' => 'Symfony',
        ],
    ];
}

/** @return list<string> */
function docsPages(array $items): array
{
    $pages = [];
    foreach ($items as $key => $value) {
        if (is_array($value)) {
            array_push($pages, ...docsPages($value));
        } elseif ($key !== '@api') {
            $pages[] = $key;
        }
    }
    return $pages;
}

function docsHref(string $page): string
{
    return match ($page) {
        '@api' => 'api.html',
        'index' => 'index.html',
        default => 'guides/' . $page . '.html',
    };
}
