# 13 GitNexus Metadata Baseline

## Purpose

Define the minimum metadata fields that future GitNexus automation should use to connect tasks, plans, commits, KB updates, and validation reports.

## Minimum Metadata Fields

| Field | Purpose | Required? | Example | Notes |
|------|---------|-----------|---------|------|
| `task_id` | Stable task identifier. | Yes | `GNX-1234` | Should be present on task, branch, commit, and validation metadata where possible. |
| `task_title` | Human-readable task name. | Yes | `Normalize Domain KB manifest` | Should be concise and stable enough for audit logs. |
| `task_type` | Classify the work. | Yes | `kb_update`, `feature`, `bugfix`, `audit`, `security` | Enables policy-specific validation. |
| `domain` | Domain or domains affected by the task. | Yes | `Auth`, `Order`, `Campaign / Coupon` | Should align with `kb-manifest.yaml` domain names. |
| `affected_paths` | Repository paths changed or reviewed by the task. | Yes | `app/Config/Routes.php` | Used to match manifest watched paths. |
| `affected_kb_files` | KB files expected to change or be reviewed. | Yes | `ai/domain-kb/06_route_baseline.md` | Derived from manifest domain mappings. |
| `plan_id` | Optional planning record identifier. | No | `PLAN-2026-04-24-001` | Useful for multi-step work or Oracle MCP plans. |
| `branch_name` | Branch associated with the task. | Yes when code or KB changes occur | `codex/domain-kb-policy` | Should be stable for the task lifecycle. |
| `commit_hash` | Commit identifier. | Yes after commit | `abc1234` | Can be empty before commit creation. |
| `validation_report` | KB or test validation report path. | Yes for KB-sensitive tasks | `ai/domain-kb/12_final_kb_readiness_check.md` | Should point to the latest applicable validation output. |
| `kb_update_required` | Whether the task requires KB changes. | Yes | `true` | Determined by manifest path matching and task type. |
| `kb_update_status` | Current KB update state. | Yes | `not_required`, `required`, `completed`, `deferred` | `deferred` must include a reason. |
| `reviewer_status` | Human or automated review state. | Yes | `pending`, `approved`, `changes_requested` | Can include Oracle MCP or human review status. |
| `risk_level` | Risk classification for the task. | Yes | `low`, `medium`, `high`, `critical` | Security, route, schema, and RBAC changes should raise risk. |
| `created_at` | Metadata creation timestamp. | Yes | `2026-04-24T12:00:00Z` | Use ISO 8601 format. |
| `updated_at` | Metadata update timestamp. | Yes | `2026-04-24T12:30:00Z` | Update when task status, KB status, or validation status changes. |

## Suggested Labels

- `kb-update-required`
- `kb-update-completed`
- `kb-drift-risk`
- `security-sensitive`
- `route-change`
- `schema-change`
- `ui-change`
- `rbac-change`
- `needs-review`
- `oracle-reviewed`

## Task to KB Link Rule

Every task should declare which domain or domains it affects.

If `affected_paths` match any `watched_paths` entry in `kb-manifest.yaml`, the related KB files must be reviewed and updated when the repository behavior changes.

If a required KB update has not been completed, the task should keep the `kb-update-required` label until one of these is true:

- The KB update is completed.
- A validation report marks the KB update as not required.
- The KB update is explicitly deferred with a reason and reviewer approval.

## Commit to KB Link Rule

Every commit should be linked to a `task_id` when possible.

If a commit changes application code, the changed paths should be checked against `kb-manifest.yaml` to determine KB impact.

If a KB update is required, the update should happen in the same branch as the code change so reviewers can evaluate implementation and documentation together.

If the commit is documentation-only, the metadata should still identify affected KB files and validation reports.

## Current Status

- GitNexus MCP is not implemented yet.
- This file defines future metadata expectations only.
- No automation exists yet.
- These fields should be treated as a baseline for future KB update policy and GitNexus integration design.
