# Issue tracker

## Location and authority

Use [Automattic/wistia-php on GitHub](https://github.com/Automattic/wistia-php/issues) as the authoritative tracker. Use existing authorized GitHub CLI authentication on `github.com`; verify the account and repository before writes. If access is unavailable, report a blocker rather than provisioning credentials or silently substituting local prose.

Setup authorizes label configuration, not backlog/spec publication, ticket creation, historical classification, closure, or graph writes. Later tracker mutations require their exact task authority. Use [label guidance](triage-labels.md) for roles, titles, and readiness.

## Native hierarchy and dependencies

GitHub sub-issues express parent/spec membership. Native blocked-by/blocking relationships express scheduling dependencies. These are separate graphs. Body links, checklists, and labels supplement them; they do not create relationships.

Before an unfamiliar operation, read the live [sub-issue API](https://docs.github.com/en/rest/issues/sub-issues) and [dependency API](https://docs.github.com/en/rest/issues/issue-dependencies). Use this procedure only for an approved graph:

1. Resolve each repository and issue through its API. A REST database ID is neither the issue number nor its GraphQL node ID. Preserve cross-repository identity.
2. Inspect all pages of current parent, child, blocked-by, and blocking relationships. Establish absence through documented API semantics; a denied or ambiguous 404 is not proof of absence.
3. Compare approved and existing edges. Skip intended existing edges and preserve unrelated edges. Conflicting parentage requires approval; never silently replace a parent.
4. Reject self-links, conflicting parents, and cycles in the combined observed-plus-planned graph. Follow a prospective blocker's transitive blockers for dependency cycles and a prospective parent's ancestors for hierarchy cycles.
5. Write only approved missing native edges. Keep server conflict checks enabled. On timeout/failure, inspect state before any retry.
6. Read back every edge in both directions, with pagination: parent's sub-issues and child's parent; dependent's blocked-by and blocker's blocking. Match actual IDs and repositories, not only command exit status.

Unsupported APIs, missing permissions, incomplete responses, or failed read-back block dependent scheduling. Report the smallest required decision/capability; do not silently replace native edges with text.

## Readiness and completion

A ready label means actionable work, not eligibility despite native blockers. A ready spec permits coordinating eligible children, not duplicate parent implementation. Reassess readiness after accepted decisions and merges against actual evidence, applying only approved metadata changes.

Opening a PR is not completion. Closure or a label alone proves neither implementation nor integration. Report verification, unresolved material findings, remaining native blockers, and integration state before recommending completion. Do not close issues or merge without applicable authority.

Update this guide when tracker location, access, relationship capability, or completion policy changes; reconcile changed authority with the maintainer first.
