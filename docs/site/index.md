# Wistia PHP

Use Automattic's PHP client to manage Wistia content, upload files, read reports and handle webhooks from a server-side application.

These docs are for PHP developers who know Composer and are new to this client. They cover the **unreleased 2.0 development API**. The published 1.x package has a different interface; do not use these examples with it.

## Start with a working read

[Make your first API call](tutorials/first-api-call.md). Install the development package, configure a token and list one page of media without changing your account.

You need **PHP 8.4+**, the **cURL extension**, Composer and a Wistia account with permission for the operation you choose. Guzzle is installed with the library. Your application owns secret storage; the client does not load `.env` files.

## Find the right guide

| I want to… | Read |
| --- | --- |
| Move an existing 1.x integration to the development API | [Migrate from 1.x](how-to/migrate-from-1x.md) |
| List, update or organize videos | [Manage media](how-to/media.md) and [work with folders](how-to/folders.md) |
| Send a file or import a URL | [Upload and import](how-to/uploads.md) |
| Change player settings or captions | [Customize a player](how-to/customizations.md) and [manage captions](how-to/accessibility.md) |
| Publish channels or run webinars | [Channels and episodes](how-to/channels.md) and [webinars](how-to/webinars.md) |
| Read performance data | [Build reports](how-to/reporting.md) |
| Connect an application or receive events | [OAuth](how-to/oauth.md) and [webhooks](how-to/webhooks.md) |
| Handle larger workflows safely | [Pagination](how-to/pagination.md), [background jobs](how-to/jobs.md) and [failures](how-to/errors.md) |
| Look up a class or supported service | [Services and operations](reference/services.md) |
| Integrate with a framework | [Plain PHP](integrations/plain-php.md), [Laravel](integrations/laravel.md) or [Symfony](integrations/symfony.md) |

## Know what the client owns

The client sends typed requests and decodes typed results. It makes **one attempt per request**, without automatic retries or redirects. Your application decides when to call it, where to store results and how to reconcile an uncertain write.

A token's scope does not guarantee feature or plan access. A queued job or accepted upload does not mean processing has completed. Read the relevant guide before running a write, deleting content or changing access.

## Development status

The implemented service families are listed in [Services and operations](reference/services.md). S3 copy requires Wistia-assisted bucket/IAM setup. Caption purchases, generated translations/localizations, audio-description orders and account trials are not exposed.

[Coverage and limitations](reference/coverage.md) describes the included surface, exclusions and limited live observations. Contract checks use synthetic responses; they do not guarantee live behavior, plan entitlement or production processing. Keep the development version pinned to a reviewed commit when evaluating it. No 2.0 release is implied by this site.
