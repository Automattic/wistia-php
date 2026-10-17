# Changelog

Notable changes are recorded here using Semantic Versioning. This changelog begins with repository revival; it does not reconstruct unverified historical release notes.

## Unreleased

- Add a Laravel 13 integration example with container bindings, a read-only Artisan command, webhook verification and isolated offline framework tests. ([#54](https://github.com/Automattic/wistia-php/issues/54))

- Add runnable plain-PHP examples for media listing, uploads, URL imports, finite job polling and webhook verification, with offline example checks. ([#53](https://github.com/Automattic/wistia-php/issues/53))

- Add five typed upload/import workflows with streamed local files, signed-URL-safe encoding and operation-specific acceptance results; S3 copy remains pending response-contract confirmation. ([#24](https://github.com/Automattic/wistia-php/issues/24))

- Add five typed webinar Analytics operations for aggregate metrics, registration series, traffic, audience pagination and engagement histograms, with local validation and offline contract coverage. ([#28](https://github.com/Automattic/wistia-php/issues/28))

- Add seven typed media Analytics operations for aggregate metrics, timeseries, embed locations, traffic, languages and cursor-paginated form conversions, with local validation and offline contract coverage. ([#27](https://github.com/Automattic/wistia-php/issues/27))

- Add five typed account Analytics operations for aggregate metrics, timeseries, content rankings, embed domains and media lookup by embed URL, with local validation and offline contract coverage. ([#26](https://github.com/Automattic/wistia-php/issues/26))

- Add all ten typed Stats operations with account/media/event/visitor metrics, inclusive by-date queries, filtered pagination and offline contract coverage. ([#25](https://github.com/Automattic/wistia-php/issues/25))

- Add typed non-purchasing bulk actions and scoped jobs with reusable resource payloads, local input limits, job-status handoff and offline contract coverage. ([#23](https://github.com/Automattic/wistia-php/issues/23))

- Add nine typed search, resource-URL, review-bundle and share-link operations with metadata filters, bundle pagination and single-attempt handling for link-creating GET requests, covered by offline contracts. ([#22](https://github.com/Automattic/wistia-php/issues/22))

- Add 11 typed account and access-management operations with nested usage data, exact credit balances, allowed-domain pagination, invitations and credential-safe experimental expiring tokens, covered by offline contracts. ([#21](https://github.com/Automattic/wistia-php/issues/21))

- Add nine typed brand and account-branding operations with explicit-null updates, application options, lazy pagination, preload selections, color-picker results and offline contract coverage. ([#20](https://github.com/Automattic/wistia-php/issues/20))

- Add ten typed webinar, collaborator and registration operations with UTC schedule validation, nested updates, filtered pagination and offline contract coverage. ([#19](https://github.com/Automattic/wistia-php/issues/19))

- Add 16 typed channel, collaborator and episode operations with podcast settings, publishing/scheduling, lazy pagination and offline contract coverage. ([#18](https://github.com/Automattic/wistia-php/issues/18))

- Add 17 typed folder, sharing and subfolder operations with nested results, lazy pagination, asynchronous copy/bulk-delete job handoff and offline contract coverage. ([#17](https://github.com/Automattic/wistia-php/issues/17))

- Add 16 typed caption, speaker, existing-localization and audio-description operations with text formats, atomic versioned edits, lazy pagination and offline contract coverage; purchases and generation remain excluded. ([#16](https://github.com/Automattic/wistia-php/issues/16))

- Add 24 typed media customization operations with nested inputs/results, replacement and partial-update/null semantics, and offline contract coverage. ([#15](https://github.com/Automattic/wistia-php/issues/15))

- Add 19 typed media lifecycle, tag, trim and deleted-media operations with nested results, local validation, lazy pagination, job handoff and move partial-error handling. ([#14](https://github.com/Automattic/wistia-php/issues/14))

- Add raw-body SHA-256 webhook verification and verified typed batch parsing, retaining unknown payloads and sanitized local failures; document application-owned routing, processing, deduplication and ordering. ([#13](https://github.com/Automattic/wistia-php/issues/13))
- Add typed OAuth authorization-URL, code-exchange and explicit refresh helpers with sanitized token/grant failures and application-owned callback/persistence boundaries. ([#12](https://github.com/Automattic/wistia-php/issues/12))
- Add bounded offset, Data-cursor (including webinar registrations), and Analytics-cursor traversal with lazy/individual-page access, preserved filters/order, and cycle/exhaustion errors. ([#11](https://github.com/Automattic/wistia-php/issues/11))
- Add typed background-job status reads and finite optional polling that exposes failed/partial bulk outcomes; stopping local polling never cancels remote work. Offline Contract tests join default discovery without enabling legacy tests. ([#11](https://github.com/Automattic/wistia-php/issues/11))
- **Breaking:** Replace the legacy magic client with the typed shared execution/configuration foundation; require PHP `^8.4`, Guzzle `^8.2` and PHPUnit `^12.5`, with no compatibility layer. ([#10](https://github.com/Automattic/wistia-php/issues/10))
- **Breaking:** Require `ext-curl` (including injected-transport installations) to preserve finite native connect/total timeouts and prevent low-level replay. The default uses fresh HTTP/1.1 connections and nonrewindable streamed bodies, rejects Guzzle recovery redispatch, and disables environment proxies and authentication/Expect negotiation.
- Reject timeout values outside the portable inclusive `0.001`–`2147483.647` second range at configuration construction; discard rate-limit integers that exceed native `PHP_INT_MAX` while retaining the ten-digit header cap. Offline redaction tests now enable argument capture in their own bootstrap.
- Add exact-name JSON/form/query primitives, sanitized typed failures with rate-limit information, single-attempt safe transport defaults, and isolated offline test discovery. ([#10](https://github.com/Automattic/wistia-php/issues/10))

- Establish maintainer and agent workflow guidance for the library's revival. No library API or dependency changes are included.

## Published history

Earlier published releases remain available in the original GitHub release records:

- [1.0.2](https://github.com/Automattic/wistia-php/releases/tag/1.0.2) — 2016-09-02
- [1.0.1](https://github.com/Automattic/wistia-php/releases/tag/1.0.1) — 2015-10-30
- [1.0.0](https://github.com/Automattic/wistia-php/releases/tag/1.0.0) — 2015-09-18

The repository also retains the `1.0.0-beta` tag; no published-release date or change summary is asserted here.
