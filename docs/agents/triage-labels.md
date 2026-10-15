# Label guidance

## Roles and titles

Use descriptive issue titles with no mandatory prefix. Preserve historical titles. `spec` and `implementation-step` are mutually exclusive:

- Apply `spec` when creating a product specification issue, then read back its presence. Publication alone does not mean ready.
- Apply `implementation-step` when creating a bounded design, implementation, or validation ticket under a spec, then read back its presence. Determine purpose and native parentage rather than copying the parent's labels.
- Standalone requests need no speculative hierarchy. Delivery PRs do not become implementation-step issues merely because they implement one.
- Assign readiness from current decisions, criteria, and evidence. Native blockers still control execution. Read back authorized assignment changes and preserve unrelated labels.

Read [tracker guidance](issue-tracker.md) before native graph operations and [release policy](workflow.md#releases-and-changelog) before classifying impact. Release labels apply to delivery PRs, using one highest applicable impact. Non-release work can have none; labels grant no release authority.

## Approved inventory

Colors are hexadecimal without the leading hash. Retained labels have empty descriptions.

| Label | Description | Color |
| --- | --- | --- |
| bug | (empty) | fc2929 |
| duplicate | (empty) | cccccc |
| enhancement | (empty) | 84b6eb |
| help-wanted | (empty) | 159818 |
| invalid | (empty) | e6e6e6 |
| question | (empty) | cc317c |
| tests | (empty) | 0052cc |
| wontfix | (empty) | ffffff |
| spec | Product specification and acceptance criteria | 5319e7 |
| implementation-step | Bounded design, implementation, or validation work under a spec | 1d76db |
| needs-triage | Scope or routing needs examination | fbca04 |
| needs-info | Required information or evidence is missing | d876e3 |
| ready-for-agent | Actionable work; native blockers still govern eligibility | 0e8a16 |
| ready-for-human | Next action requires human input or capability | f9d0c4 |
| patch | Backward-compatible bug fix | bfdadc |
| minor | Backward-compatible functionality | a2eeef |
| major | Incompatible public change | d93f0b |

`wontfix` records explicitly declined work with rationale. `help-wanted` invites contributions without asserting readiness or granting merge rights.

## Audit decisions and maintenance

Setup retains existing bug, duplicate, enhancement, invalid, question, tests, and wontfix metadata and assignments. It renames `help wanted` to `help-wanted` in place and adds the nine workflow/release labels above. No labels are consolidated/deleted; no historical issues are reclassified and no graph edges are created.

The renamed label had no assignments or repository-local consumers at audit. The maintainer knew of no automation; external automation was not independently verified. No label-driven release automation was found locally.

Before future label changes, inspect all labels and all open/closed issue and PR assignments, local consumers, and relevant external automation. Propose exact metadata, assignment migrations, removals, and consumer effects for approval. Re-read before writing; preserve unrelated metadata/history and verify resulting labels and assignments afterward. Renaming/deleting requires specific approval; missing old spelling after this approved rename is not a reason to recreate it.
