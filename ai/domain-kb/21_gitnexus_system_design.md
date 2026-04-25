# 21 GitNexus System Design

## Purpose

Define how tasks, plans, repository changes, KB updates, and commits are connected.

## Core Concept

Task is the central unit.

Everything starts from a task. A task defines why work exists, which domain it belongs to, which paths are expected to change, which KB files may be affected, and whether a KB update is required before commit. Plans, repository changes, KB update reports, validation reports, and commits should all link back to the task.

## Task Structure

| Field | Description | Required | Example |
|-------|-------------|----------|---------|
| `task_id` | Stable task identifier used across plans, branches, KB updates, reports, and commits. | Yes | `GNX-1234` |
| `title` | Short human-readable task title. | Yes | `Add product stock warning` |
| `description` | Detailed task intent and expected behavior. | Yes | `Show low-stock warning on product admin screens.` |
| `domain` | Primary affected domain or domain list. | Yes | `Product / Catalog` |
| `affected_paths` | Repository paths expected to change or actually changed. | Yes | `app/Models/ProductsModel.php` |
| `affected_kb_files` | KB files mapped from affected paths through `kb-manifest.yaml`. | Yes when KB impact exists | `ai/domain-kb/10_schema_model_matrix.md` |
| `priority` | Task priority for planning and review. | Recommended | `medium` |
| `risk_level` | Risk classification used for review depth. | Yes | `high` |
| `status` | Task lifecycle status. | Yes | `in_progress` |
| `created_at` | Task creation timestamp. | Yes | `2026-04-25T10:00:00+03:00` |

## Task Lifecycle

1. Task created
2. Domains identified
3. Plan generated
4. Implementation started
5. Changes made
6. `changed_paths` detected
7. KB update triggered
8. KB update report generated
9. Commit linked
10. Task closed

## Plan Structure

| Field | Description |
|-------|-------------|
| `plan_id` | Stable plan identifier linked to `task_id`. |
| `task_id` | Parent task identifier. |
| `steps` | Ordered implementation or documentation steps. |
| `expected_changes` | Expected repository changes by path or component. |
| `kb_impact` | Expected KB impact derived from `affected_paths` and `kb-manifest.yaml`. |
| `risk_assessment` | Security, route, schema, domain, or UI risk summary. |

## Task to KB Mapping

- A task must include `affected_paths`.
- `affected_paths` are matched against `ai/domain-kb/kb-manifest.yaml`.
- Manifest matching identifies affected domains and affected KB files.
- KB update must run before commit when affected paths imply domain, route, security, schema, or backend-flow documentation changes.
- KB update reports should be stored under `ai/domain-kb/updates/`.
- If no KB update is required, the task must record an explicit skip reason.

Mapping flow:

```text
task_id
-> affected_paths
-> kb-manifest.yaml
-> affected domains
-> affected KB files
-> KB update report
-> commit metadata
```

## Task to Commit Link

- Commit metadata must include `task_id`.
- Commit metadata must include KB update status.
- Commit should reference the KB update report path when a KB update was required.
- Commit must not be allowed if KB update is missing for changed paths that require KB review.
- If KB update is skipped, the commit/task metadata must include the skip reason.
- If KB drift is detected after commit, the task should reopen or create a follow-up task.

## Task to KB Update Rules

KB update required for:

- Route changes
- Model changes
- Security changes
- Permission/RBAC changes
- Migration/schema changes
- UI that implies backend behavior
- New feature addition that changes domain behavior

KB update optional for:

- Pure UI text changes
- Pure style changes
- Pure refactor with no behavior change
- Test-only changes
- Dead code removal with no active route/domain ownership impact

Optional updates still require an explicit `kb-update-not-required` or skipped status with a reason.

## Failure Conditions

- Task without `affected_paths`.
- Commit without KB update when KB update is required.
- KB drift detected after repository changes.
- Wrong domain mapping from changed paths.
- Missing KB update report for a task that changed domain-owned paths.
- Commit metadata missing `task_id`.
- KB update marked completed without checking affected KB files.
- Review-required domain auto-updated without concrete diff evidence.

## Minimal First Implementation

- Manual task creation.
- Manual `changed_paths` input.
- Manual KB update trigger.
- Manual KB update report creation.
- Manual commit metadata check.
- No MCP yet.
- No automated git diff extraction yet.
- No CI gate yet.

This first implementation should reuse the validated KB Update system behavior from the controlled tests:

- Product/model and storefront view updates should update only domain/schema/claim KB files.
- Security filter updates should update security, route-permission, and claim KB files.
- Route config updates should update route matrix, route baseline, and claim KB files.
- Review-required domains should remain manual until exact evidence exists.

## Future Integration

- Oracle MCP:
  - Provide repository-aware guidance and cross-check KB update decisions.
  - Review high-risk domain, security, and route changes.
- Git hooks:
  - Detect changed paths before commit.
  - Warn when KB update status is missing.
- CI/CD:
  - Run KB drift checks.
  - Fail or warn when code changes lack required KB updates.
- Automated changed_paths extraction:
  - Replace manual path entry.
  - Feed exact changed paths into the KB update skill.
  - Reduce human error in impact mapping.

## Final Summary

GitNexus is required after the KB system because the KB now knows how to map repository paths to domains and documentation updates, but it still needs a task-centered coordination layer. GitNexus provides that layer by connecting task intent, changed paths, plans, KB update reports, commit metadata, and validation status.

Without GitNexus, KB updates can be correct in isolation but hard to trace across real work. With GitNexus, every repository change can answer:

- Which task caused this change?
- Which domains were affected?
- Which KB files were reviewed or updated?
- Which report proves the KB update was completed or intentionally skipped?
- Which commit closed the loop?

This prepares the project for controlled KB drift prevention, safer commit workflows, and later Oracle MCP or CI integration.
