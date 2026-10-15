# Working agreement

## Authority and delivery

Use short-lived branches and pull requests into `main`; there is no persistent development branch. Fetch and inspect the working tree and branch before implementation. Preserve unrelated work. Never reset, rebase, force-push, discard, or move another person's work implicitly.

Within requested scope, agents may inspect, plan, execute ordinary skills, implement, run safe local checks, delegate bounded tasks, obtain independent read-only review, and perform lead-owned repairs without repeated permission prompts. Respect each skill's invocation gate and runtime restrictions.

- At most two delegated workers may run concurrently.
- Assign implementers exclusive bounded scope. Reviewers remain independent and read-only, return separately inspectable findings, and do not repair their own findings or recursively delegate.
- The lead inspects findings, integrates work, fixes valid in-scope defects, reruns affected checks, and renews affected review after significant repairs.
- At most two repair/re-review rounds per change; escalate unresolved findings afterward. Material unresolved findings block acceptance. Defer nonessential suggestions explicitly with rationale.
- A review-only request stays review-only. Repair authority operates under a delivery or review-and-fix request.
- Auxiliary assessments remain governed by [model-use policy](model-use.md), separately from ordinary agents.

New sensitive disclosure, spending, installations, credentials, deployment, destructive operations, and repository/tracker mutations outside an exact approved plan require explicit approval. Commit, push, and PR publication need task or standing authority covering their scope; setup approval covers only the setup documents and setup PR. No automatic merge, release, or issue closure follows from delivery.

## Main-branch gate

The approved gate requires pull requests and resolved review conversations, zero mandatory human approvals, and no bypass actors. No required CI checks are configured during setup; add them only after real modern checks exist and their requirements are approved. Inspect live rules before relying on enforcement; a failed or unverifiable control is a blocker, not a reason to claim compliance. Administrators retain the ability to edit rules.

The setup outcome and live GitHub settings establish enforcement, not this document alone. Existing PRs must be preserved when changing branch conventions.

## Verification

Choose checks by meaningful failure risk and existing coverage. Add tests for distinct unprotected behavior, not a test-count quota. Report exact checks, results, unrun checks, limitations, unresolved findings, and documentation impact. Lead inspection is not independent review.

Current verification sources are [composer.json](../../composer.json), [phpunit.xml.dist](../../phpunit.xml.dist), and [tests](../../tests/). The README's PHPUnit instructions run live integration tests that create/delete Wistia resources and upload data. Do not run them casually, install dependencies, or configure credentials under ordinary safe-check authority. Live/destructive/chargeable operations need the applicable separate approval. Do not request credentials in chat.

Safe inspection includes `git diff --check` and checking documentation links. No modern offline suite or repository-local CI definition exists at setup. Legacy application tests were not run for documentation setup. Do not describe static checks as live API or agent-runtime verification.

## Releases and changelog

Adopt Semantic Versioning for this single library. Published tags/releases are the historical version evidence; [composer.json](../../composer.json) owns package identity and dependency requirements and currently has no explicit version. [CHANGELOG.md](../../CHANGELOG.md) is the manually maintained notable-change record. No repository-local release automation was found; external automation remains unknown.

Implementation adds an Unreleased entry for notable user-facing changes; review checks the entry and impact. Documentation-only/internal changes may need no release-impact label, with a reason. On delivery PRs, use one highest applicable impact: patch for backward-compatible fixes, minor for backward-compatible functionality, major for incompatible public changes. Labels classify changes; they never authorize a version bump or publication.

The planned breaking rewrite targets 2.0.0. Setup does not bump a version, tag, or release. Release preparation must reconcile aggregate changes with this policy and obtain explicit release approval. Preserve published history; do not invent historical changes or dates from version-looking tags. No new prerelease exception is established; resolve one before using it.

## Documentation upkeep

Keep one authoritative home for each fact:

| Change | Owner and action |
| --- | --- |
| Purpose, ownership, operating constraint | Lead updates [project context](project.md) after the relevant approval. |
| Authority or delivery/check procedure | Lead/implementation updates this agreement and verifies safe commands; authority changes need approval. |
| Auxiliary assessment permission | Lead reconciles [model-use policy](model-use.md) through exact approval. |
| Product scope or acceptance | Delivery lead updates the authoritative spec with the product decision-maker's approval. |
| Domain meaning or enduring architectural decision | Discover `domain-modeling` when such records become necessary; follow its approval gates before creating/updating a glossary/context map or ADR. Reading existing definitions alone needs no modeling workflow. Missing capability blocks affected modeling work. |
| Spec/ticket classification and readiness | Spec/ticket owner follows [tracker](issue-tracker.md) and [label](triage-labels.md) rules and reads back assignments. |
| Dependencies or completion | Ticketing/delivery owner updates native relationships and actual evidence together within approved scope. |
| Notable user-facing change | Implementation updates the changelog; review checks impact and entry. |
| Release convention or automation | Lead reconciles policy, labels, automation, and affected assignments through an approved plan. |

When code and docs disagree, determine whether code is wrong, docs are stale, or a decision is unresolved; resolve within authority or escalate. Report changed documents or explain why none require changes. Revisit facts when inputs change, not on an arbitrary paperwork schedule.
