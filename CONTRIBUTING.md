# Contributing to Wistia PHP

Bug reports, documentation fixes and code contributions are welcome. This guide covers contributions to the 2.x client. For help using the library, start with the [documentation](https://wistia-php.space.fast/).

## Report a bug or suggest a change

Search the [existing issues](https://github.com/Automattic/wistia-php/issues) before opening a new one.

For a bug report, include:

- Your PHP and Wistia PHP versions.
- A small example that reproduces the problem.
- What you expected and what happened instead, including the exception or error message.
- Whether the problem occurs with a synthetic response or a real Wistia request.

Remove API tokens, authorization headers, webhook secrets and private account or media data from examples and logs. A report does not need live credentials.

For a new feature or a change to the public API, open an issue to discuss the intended behavior before starting a large implementation. Include the Wistia API documentation for the operation where applicable.

## Set up a local checkout

You need Git, Composer and PHP 8.4 or newer with the cURL extension. Composer will report any additional PHP extensions required by the development dependencies.

Fork the repository on GitHub, then clone your fork and create a branch from `main`:

```sh
git clone https://github.com/YOUR-USERNAME/wistia-php.git
cd wistia-php
git switch -c fix/describe-your-change
composer install
```

Use a short-lived branch for each contribution. Pull requests target `main`; there is no separate development branch.

Composer installs Guzzle and PHPUnit. Dependency installation requires network access, but the default tests use synthetic responses and need no Wistia account, API token or `.env` file. Keep generated dependencies, build output and local credentials out of your commits. This library ignores its local `composer.lock`; applications consuming it should keep their own lock files.

## Make and test your change

Follow the style of the surrounding code and keep the change focused. Preserve the `Automattic\Wistia` namespace and `automattic/wistia-php` package name.

Add tests for new behavior and regression tests for bug fixes. The default suite includes:

- `tests/Offline/` for local behavior such as validation and helpers.
- `tests/Contract/` for request and response behavior using synthetic HTTP transports.

For API changes, check the HTTP method and path, request encoding, typed results and error handling. Use synthetic fixtures rather than real account data. The [internal contract and coverage records](docs/rewrite/README.md) document supported operations and known verification limits.

Run the tests from the repository root:

```sh
composer test
```

To also fail on deprecations and notices:

```sh
composer test -- --fail-on-deprecation --fail-on-notice
```

The default configuration already fails on warnings and risky tests. Where available, check changes on both PHP 8.4 and 8.5, and report the versions you actually tested. Passing synthetic tests does not establish live API behavior, account-plan access or processing completion.

> [!CAUTION]
> Historical tests in `tests/ClientTest.php` can upload files and create or delete Wistia resources. They are excluded from the default suite, separately gated and incompatible with the 2.x client. Do not run the legacy configuration as a routine check or supply live credentials to run tests. Any live or destructive testing needs explicit maintainer approval for the particular account and operations.

## Update documentation

Update the relevant usage guide or example when behavior changes. Public website sources live in `docs/site/`; generated pages in `build/` are not committed. See [documentation build and publishing](tools/docs/README.md) for build prerequisites, local preview commands and maintainer publishing instructions. Building or previewing locally does not require publishing the site.

Check local Markdown links with:

```sh
php tools/docs/check-repository-links.php
```

For a notable user-facing change, add a concise entry under **Unreleased** in [CHANGELOG.md](CHANGELOG.md). Describe what changes for users. Documentation-only and internal maintenance changes usually do not need an entry. Leave published release notes and version tags unchanged.

## Submit a pull request

Push your branch to your fork and open a pull request against `Automattic/wistia-php:main`.

In the description, explain:

- What the change does and why it is needed; link a related issue if there is one.
- How you tested it, including PHP versions, commands and any checks you could not run.
- Any compatibility changes, documentation updates or verification limits.

Review the diff for unrelated edits, generated files and secrets before submitting. Maintainers may ask for changes; respond to review comments and push updates to the same branch.

Changes enter `main` through pull requests with review conversations resolved. Maintainers handle merging and releases; a merged contribution does not itself publish a new package version.

## Using a coding assistant

Coding agents should read this guide and [AGENTS.md](AGENTS.md), which provides the additional project instructions for agent-assisted work.
