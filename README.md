# Wistia PHP

[![Latest Release](https://img.shields.io/github/release/Automattic/wistia-php.svg?style=flat-square)](https://github.com/Automattic/wistia-php/releases)
[![PHP](https://img.shields.io/badge/php-%5E8.4-777BB4?style=flat-square)](composer.json)

A framework-independent PHP client for Wistia, with typed requests and results for content management, uploads, reporting and webhooks.

> [!IMPORTANT]
> Wistia PHP **2.0** requires PHP **8.4+**, the **cURL extension** and Composer. It is a breaking rewrite of 1.x with no legacy compatibility layer; follow the migration guide when upgrading.

## Documentation

**[Read the Wistia PHP documentation →](https://wistia-php.space.fast/)**

- [Install the client and make your first API call](https://wistia-php.space.fast/guides/tutorials/first-api-call.html)
- [Migrate from the 1.x client](https://wistia-php.space.fast/guides/how-to/migrate-from-1x.html)
- [Review coverage and verification limits](https://wistia-php.space.fast/guides/reference/coverage.html)
- [Find a workflow guide](https://wistia-php.space.fast/#find-the-right-guide)
- [Browse the PHP API reference](https://wistia-php.space.fast/api.html)
- [Use the plain-PHP, Laravel or Symfony examples](https://wistia-php.space.fast/#find-the-right-guide)

The website covers the 2.0 API. Follow its installation instructions rather than combining these examples with the 1.x package. Your application owns credentials and secret storage; the library does not load `.env` files.

## Development

With the declared development dependencies provisioned:

```sh
composer test
```

Default discovery runs only offline foundation, helper and contract suites using synthetic responses. It loads no live credentials and does not establish Wistia account access, plan entitlement or processing completion.

> [!CAUTION]
> Historical legacy tests can upload files and create or delete Wistia resources. They are excluded from default discovery, separately gated and incompatible with the rewrite. Do not run them as ordinary checks or supply credentials for local verification.

Maintainers: [documentation build and publishing](tools/docs/README.md), [rewrite contracts and coverage](docs/rewrite/README.md), and [agent guidance](AGENTS.md). Website sources live in `docs/site/`; they are retained for reproducible builds, not as a second user documentation site.

---

Copyright (C) 2015–2026 Automattic. Distributed under [GPL-2.0-or-later](LICENSE).
