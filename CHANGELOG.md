# Changelog

Notable changes are recorded here using Semantic Versioning. Unreleased notes describe the next release, not a published package; verified earlier releases are listed under Published history.

## Unreleased

No unreleased changes.

<a id="200-unreleased"></a>

## 2.0.0 - 2026-10-21

### Breaking changes

- Replace the legacy magic client, generic endpoint dispatch and raw-object/integer-error returns with explicit services, typed requests/results and exceptions; no compatibility layer is provided. ([#10](https://github.com/Automattic/wistia-php/issues/10))
- Require PHP `^8.4`, `ext-curl` and Guzzle `^8.2`, including when an application injects its own transport. ([#10](https://github.com/Automattic/wistia-php/issues/10))
- Use Bearer authentication and the modern `2026-09` contract for Data, Stats and Analytics, while retaining separate Upload/OAuth hosts, encoding and wire names. ([#8](https://github.com/Automattic/wistia-php/issues/8))

### Added

- Add media lifecycle, tags, trims and deleted-media operations with typed nested results, validation, pagination and asynchronous job handoff. ([#14](https://github.com/Automattic/wistia-php/issues/14))
- Add player-customization operations with typed inputs/results, replacement and partial-update semantics, and explicit-null handling. ([#15](https://github.com/Automattic/wistia-php/issues/15))
- Add caption text formats and versioned edits, speaker profiles, and management of existing localizations and audio descriptions without purchasing or generating them. ([#16](https://github.com/Automattic/wistia-php/issues/16))
- Add folder/sharing/subfolder management, channel/episode publishing and webinar/registration workflows. ([#17](https://github.com/Automattic/wistia-php/issues/17), [#18](https://github.com/Automattic/wistia-php/issues/18), [#19](https://github.com/Automattic/wistia-php/issues/19))
- Add brands, account branding, usage/credit reads, allowed domains, contact invitations and explicitly experimental expiring tokens. ([#20](https://github.com/Automattic/wistia-php/issues/20), [#21](https://github.com/Automattic/wistia-php/issues/21))
- Add search, resource URLs, review bundles and share-link workflows with typed results and write-safe execution. ([#22](https://github.com/Automattic/wistia-php/issues/22))
- Add non-purchasing bulk actions and jobs with local limits and observable partial results. ([#23](https://github.com/Automattic/wistia-php/issues/23))
- Add six Upload workflows: streamed local files, direct URLs, thumbnail files/URLs, S3 copy and supplied localization audio, with operation-specific results. ([#24](https://github.com/Automattic/wistia-php/issues/24))
- Add all ten Stats operations and 17 account, media and webinar Analytics operations, with typed metrics, filters and endpoint-specific pagination. ([#25](https://github.com/Automattic/wistia-php/issues/25), [#26](https://github.com/Automattic/wistia-php/issues/26), [#27](https://github.com/Automattic/wistia-php/issues/27), [#28](https://github.com/Automattic/wistia-php/issues/28))
- Add bounded offset/cursor pagination and finite background-job polling with completion, reported failure, partial failure and exhaustion outcomes. ([#11](https://github.com/Automattic/wistia-php/issues/11))
- Add OAuth authorization-URL, code-exchange and explicit refresh helpers with application-owned callbacks and token persistence. ([#12](https://github.com/Automattic/wistia-php/issues/12))
- Add original-body SHA-256 webhook signature verification and typed batch parsing, retaining unknown event payloads and application-owned processing responsibilities. ([#13](https://github.com/Automattic/wistia-php/issues/13))
- Add runnable plain-PHP, Laravel 13 and Symfony 8.1 integration examples with offline checks. ([#53](https://github.com/Automattic/wistia-php/issues/53), [#54](https://github.com/Automattic/wistia-php/issues/54), [#55](https://github.com/Automattic/wistia-php/issues/55))
- Add a documentation site, a 1.x migration guide, explicit coverage/exclusion records and repeatable inventory-to-implementation checks. ([#29](https://github.com/Automattic/wistia-php/issues/29), [#60](https://github.com/Automattic/wistia-php/pull/60))

### Changed

- Remove the personal author entry from Composer metadata at the maintainer's request.
- Run only credential-free offline foundation, helper and contract suites by default using PHPUnit `^12.5`; keep historical live/destructive tests separately gated and incompatible with the rewrite. ([#10](https://github.com/Automattic/wistia-php/issues/10))
- Retain exact endpoint field names, signed-URL encoding and omission/null distinctions rather than applying global request/response conversions. ([#10](https://github.com/Automattic/wistia-php/issues/10))

### Fixed

- Correct Composer's license identifier to `GPL-2.0-or-later`, matching the existing license grant.
- Validate portable timeout bounds and discard rate-limit integers exceeding native integer limits. ([#10](https://github.com/Automattic/wistia-php/issues/10))
- Present release-oriented documentation with footer-only publisher credit, centered headers/footers and padded sidebar labels. ([#63](https://github.com/Automattic/wistia-php/pull/63), [#64](https://github.com/Automattic/wistia-php/pull/64))

### Security

- Use a single-attempt default transport without automatic retries, redirects or environment-proxy fallback, and expose uncertain remote outcomes for ambiguous writes. ([#10](https://github.com/Automattic/wistia-php/issues/10))
- Keep credentials, signed URLs and raw remote failures out of library-generated error context while preserving typed failure distinctions and supplied rate-limit hints. ([#10](https://github.com/Automattic/wistia-php/issues/10))

Read the [migration guide](https://wistia-php.space.fast/guides/how-to/migrate-from-1x.html) before upgrading from 1.x. [Coverage and verification limits](https://wistia-php.space.fast/guides/reference/coverage.html) distinguish offline checks, limited standalone live observations and unverified live API/plan behavior; purchases, generated translation/dubbing and account trials remain excluded.

## Published history

Earlier published releases remain available in the original GitHub release records:

- [1.0.2](https://github.com/Automattic/wistia-php/releases/tag/1.0.2) — 2016-09-02
- [1.0.1](https://github.com/Automattic/wistia-php/releases/tag/1.0.1) — 2015-10-30
- [1.0.0](https://github.com/Automattic/wistia-php/releases/tag/1.0.0) — 2015-09-18

The repository also retains the `1.0.0-beta` tag; no published-release date or change summary is asserted here.
