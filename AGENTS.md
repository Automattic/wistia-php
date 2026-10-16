# Agent guidance

Before planning or implementing, read [project context](docs/agents/project.md) and [workflow](docs/agents/workflow.md).

- Before creating, classifying, or scheduling issues, read [tracker guidance](docs/agents/issue-tracker.md) and [label guidance](docs/agents/triage-labels.md).
- Before proposing or using auxiliary model assessments, managing their services, or changing their permissions, read [model-use policy](docs/agents/model-use.md).
- Before classifying release impact or updating the changelog, read the workflow's release policy and [CHANGELOG.md](CHANGELOG.md).
- Update each authoritative document when its facts or approved decisions change. Report documentation impact at completion.

The historical legacy tests make live, potentially destructive Wistia calls and remain separately gated; do not run them as an ordinary local check. Default discovery selects only the offline foundation, helper and contract suites. Follow the workflow's verification boundaries.
