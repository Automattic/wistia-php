# Wistia PHP

[![Latest Release](https://img.shields.io/github/release/Automattic/wistia-php.svg?style=flat-square)](https://github.com/Automattic/wistia-php/releases)
[![PHP](https://img.shields.io/badge/php-%5E8.4-777BB4?style=flat-square)](composer.json)

A framework-independent PHP client for Wistia's server-side Data, Upload, Stats and Analytics APIs, with typed requests/results, OAuth helpers and webhook verification.

> [!IMPORTANT]
> **2.0.0 is a breaking rewrite of 1.x**, with no legacy compatibility layer. Read the [migration guide](https://wistia-php.space.fast/guides/how-to/migrate-from-1x.html) before upgrading and the [2.0.0 release notes](CHANGELOG.md#200---2026-10-21) for an overview of the changes.

## Installation

Requires **PHP 8.4+**, the **cURL extension** and Composer:

```sh
composer require 'automattic/wistia-php:^2.0'
```

Composer installs Guzzle as a dependency. Keep your application's lock file under version control to reproduce the resolved versions.

## Documentation

**[Read the Wistia PHP documentation →](https://wistia-php.space.fast/)**

- [Install the client and make your first API call](https://wistia-php.space.fast/guides/tutorials/first-api-call.html)
- [Migrate from the 1.x client](https://wistia-php.space.fast/guides/how-to/migrate-from-1x.html)
- [Review coverage and verification limits](https://wistia-php.space.fast/guides/reference/coverage.html)
- [Find a workflow guide](https://wistia-php.space.fast/#find-the-right-guide)
- [Browse the PHP API reference](https://wistia-php.space.fast/api.html)
- [Use the plain-PHP, Laravel or Symfony examples](https://wistia-php.space.fast/#find-the-right-guide)

The website covers the 2.0 API; do not combine its examples with the 1.x package. Framework integrations are optional examples, not library dependencies.

Your application owns credentials, secret storage and callback/webhook routes. The library does not load `.env` files, automatically retry requests or follow redirects. Purchases, generated translation/dubbing and account trials are excluded. Offline contract coverage does not establish live API behavior or plan entitlement; see the coverage guide for the exact verification limits.

## Contributing

See [Contributing to Wistia PHP](CONTRIBUTING.md) for local setup, testing and pull-request guidance.

---

Copyright (C) 2015–2026 Automattic. Distributed under [GPL-2.0-or-later](LICENSE).
