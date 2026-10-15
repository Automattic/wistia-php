# Project context

## Purpose and stage

Wistia PHP is a framework-independent PHP client distributed as `automattic/wistia-php` through Composer. It serves PHP applications integrating with Wistia. Usage scale is not established.

This is a legacy library being prepared for revival. The current source uses Guzzle 6, magic API dispatch, and PHPUnit 4.8 tests. Read [composer.json](../../composer.json), [src/Client.php](../../src/Client.php), and [README.md](../../README.md) for the existing implementation, not the planned replacement.

The agreed direction is a breaking rewrite for PHP 8.4+ and the supported server-side Wistia APIs, targeting 2.0.0. Setup does not implement that rewrite, publish its specification, or create implementation tickets. Formal specification publication remains a separate decision.

## Ownership and collaboration

The primary maintainer is the final product decision-maker. Agents own routine engineering within the requested scope; explain consequential choices in product terms and escalate scope, cost, security, and operating commitments.

Organization members retain repository admin access. External contributions are welcome through pull requests; outside contributors are not granted merge access. Administrators can change repository rules; PR requirements do not remove that capability.

## Constraints and risks

- Preserve package identity, existing license/copyright, and published history unless a separately approved change says otherwise.
- Consumers own their application hosting and integration; no platform-specific architecture is required.
- API credentials, uploads, media metadata, and webhook payloads may contain sensitive information. Keep credentials out of chat, logs, fixtures, and commits.
- Current tests can create/delete projects and upload media. Never treat them as credential-free unit tests.
- No Wistia test account or credentials have been supplied for this setup. Live API behavior is unverified.
- API changes, ambiguous write failures, account-plan restrictions, and charge-incurring operations require explicit contracts and meaningful checks during rewrite planning. Setup establishes none of those endpoint contracts.
- External automation is not fully known. Inspect affected integrations before changing branch, label, release, or API conventions.

Update this document when purpose, maintenance ownership, operating constraints, or confirmed revival scope changes; obtain approval for consequential changes.
