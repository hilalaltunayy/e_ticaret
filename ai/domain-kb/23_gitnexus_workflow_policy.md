# 23 GitNexus Workflow Policy

## Purpose

Define the workflow rules for task, plan, branch, commit, validation, and KB update linkage.

## Task Status Model

| Status | Meaning | Entry Condition | Exit Condition |
|--------|---------|-----------------|----------------|
| `draft` | Task is captured but not ready for planning. | Task idea exists with partial metadata. | Required task metadata is completed. |
| `ready_for_plan` | Task has enough metadata to create a plan. | `task_id`, title, domain, task type, priority, risk level, and initial `affected_paths` or expected paths are present. | Plan is created. |
| `planned` | Task has an approved or accepted plan. | Plan includes steps, expected changes, KB impact, and risk assessment. | Implementation begins. |
| `in_progress` | Work is actively being performed. | Branch exists or manual work starts. | Changed paths are detected or provided. |
| `kb_update_required` | Changed paths require KB review or update. | `affected_paths` match `kb-manifest.yaml` entries that require KB review. | KB update is completed or explicitly skipped with a reason. |
| `validation_required` | Task requires validation before commit. | Risk level, route/security/schema impact, or reviewer policy requires validation. | Validation report is generated and linked. |
| `ready_for_commit` | Task is ready to commit. | Required KB update, validation, and approval gates are satisfied. | Commit is created and linked. |
| `committed` | Commit exists and is linked to task metadata. | Commit includes `task_id` and KB update status. | Post-commit checks pass and task can close. |
| `closed` | Task is complete. | Commit, KB update, validation, and approval metadata are complete. | No exit condition unless reopened by drift or review. |
| `blocked` | Task cannot proceed. | Required metadata, KB report, validation report, approval, or changed paths are missing. | Blocking issue is resolved and task returns to the appropriate prior status. |

## Task Type Model

| Task Type | Meaning | Allowed Branch Prefix | Commit Type | Notes |
|----------|---------|-----------------------|-------------|------|
| `feature` | Adds or changes user-visible behavior. | `feature/` | `feat` | Requires KB review when domain behavior, routes, schema, services, or backend-backed UI changes. |
| `fix` | Corrects a defect or broken behavior. | `fix/` | `fix` | Risk level depends on affected domain and changed paths. |
| `docs` | Updates documentation only. | `docs/` | `docs` | Must not modify application code. |
| `refactor` | Restructures code without intended behavior change. | `chore/` or `feature/` when domain-facing | `refactor` | KB update can be skipped only with an explicit no-behavior-change reason. |
| `chore` | Maintenance work that is not feature, fix, docs, or refactor. | `chore/` | `chore` | KB impact depends on changed paths. |
| `kb_update` | Updates Domain KB files or KB reports. | `docs/` | `docs` | Uses docs branch prefix because it is documentation-only work. |
| `security` | Changes authentication, authorization, RBAC, filters, session, CSRF, or sensitive access behavior. | `fix/` | `fix` | Automatically requires validation. |
| `route` | Changes route definitions, route groups, controller bindings, or route filters. | `feature/` or `fix/` | `feat` or `fix` | Automatically requires validation. |
| `schema` | Changes models, migrations, seeders, or database mapping. | `feature/` or `fix/` | `feat` or `fix` | Automatically requires validation. |
| `ui` | Changes views, layout, styling, copy, or frontend interaction. | `feature/` or `chore/` | `feat` or `chore` | Must be upgraded to `feature` or `route` if it implies backend-flow behavior. |

Rules:

- `task_type` is a required metadata field.
- Branch type must align with `task_type`.
- Commit type must align with `task_type`.
- `security`, `route`, and `schema` tasks are automatically treated as `validation_required`.
- `kb_update` tasks may use the `docs/` branch prefix.
- `ui` tasks must be upgraded to `feature` or `route` if they create backend-flow impact.

## Branch Naming Rule

Recommended formats:

- `feature/GNX-1234-short-title`
- `fix/GNX-1234-short-title`
- `docs/GNX-1234-short-title`
- `chore/GNX-1234-short-title`

Rules:

- Branch name must include `task_id`.
- Branch type must match `task_type`.
- Branch naming uses `task_type` through the allowed branch prefix in the Task Type Model.
- Short title should be lowercase kebab-case.
- Branch name should remain stable for the task lifecycle.
- Documentation-only KB work should use `docs/GNX-1234-short-title`.
- Security or bugfix work should use `fix/GNX-1234-short-title` unless the task type says otherwise.

## Commit Message Format

Format:

```text
GNX-1234: type(scope): short summary
```

Examples:

- `GNX-1234: feat(product): add low stock warning`
- `GNX-1235: docs(kb): update route baseline`
- `GNX-1236: fix(auth): correct secretary access guard`

Rules:

- Must include `task_id`.
- Must include type.
- Must include scope/domain.
- Commit messages should use the primary task ID when multiple tasks are linked.
- Related tasks must be listed in the commit body.
- Must mention KB update status in commit body if KB update was required.
- Should reference KB update report path when available.
- Should reference validation report path when validation was required.

Recommended commit body fields:

```text
Task: GNX-1234
KB-Update-Status: completed
KB-Update-Report: ai/domain-kb/updates/YYYY-MM-DD_GNX-1234_kb_update_report.md
Validation-Report: ai/domain-kb/updates/YYYY-MM-DD_GNX-1234_validation_report.md
Risk-Level: high
Reviewer-Status: approved
```

## Multi-Task / Multi-Commit Linkage

Rules:

- One task may produce multiple commits.
- One task may produce multiple KB update reports if work happens in phases.
- One commit should normally belong to one primary task.
- One commit may reference multiple task IDs only when the change intentionally closes or affects multiple tasks.
- When multiple task IDs are used, one must be marked as primary.
- Commit body must include:
  - `Primary-Task`
  - `Related-Tasks`
  - `KB-Update-Status`
  - `KB-Update-Report`
- If different tasks affect different domains, KB update report must list domain impact per task.
- A task cannot be closed until all linked commits and required KB reports are complete.
- If a follow-up commit changes affected paths, KB update must be re-evaluated.
- If a commit references multiple tasks but lacks per-task KB impact, status should be `Needs Review`.

Example commit body:

```text
Primary-Task: GNX-1234
Related-Tasks: GNX-1235, GNX-1236
KB-Update-Status: completed
KB-Update-Report: ai/domain-kb/updates/YYYY-MM-DD_GNX-1234_kb_update_report.md
Risk-Level: medium
Reviewer-Status: approved
```

## KB Update Report Link Rule

- If KB update is required, task metadata must include `update_report_path`.
- Report must live under `ai/domain-kb/updates/`.
- Commit body should reference the report path.
- If KB update is skipped, skip reason must be recorded in task metadata and commit body.
- KB update report must identify:
  - `task_id`
  - changed paths
  - affected domains
  - affected KB files
  - updates applied
  - skipped updates
  - final status
- A task cannot move to `ready_for_commit` while a required KB update report is missing.

## Validation Report Link Rule

- High-risk tasks require validation report.
- Security tasks require validation report.
- Route tasks require validation report.
- Schema/model tasks require validation report.
- Validation report path must be linked in task metadata.
- Commit body should reference validation report if available.
- Validation report should live under `ai/domain-kb/updates/` unless a later policy defines a dedicated validation directory.
- A task cannot move to `ready_for_commit` while a required validation report is missing.

## Reviewer / Approval Status

Allowed statuses:

- `not_required`
- `pending`
- `approved`
- `changes_requested`

Rules:

- High risk requires approval.
- Critical risk requires approval.
- Security-sensitive tasks require approval.
- Route baseline changes require review.
- Schema/model changes require review.
- `changes_requested` blocks `ready_for_commit`.
- `pending` blocks `ready_for_commit` when approval is required.
- `not_required` is allowed only for low-risk tasks or explicitly skipped review with a reason.

## Risk Level Workflow Impact

| Risk Level | Required KB Update? | Required Validation? | Required Reviewer Approval? | Can Commit Proceed? |
|------------|---------------------|----------------------|-----------------------------|---------------------|
| `low` | Required if manifest impact exists; otherwise optional with skip reason. | Not required unless route/security/schema impact exists. | Usually not required. | Yes, if KB status is completed or skipped with reason. |
| `medium` | Required if domain behavior, route binding, model, service, or UI-backed behavior changes. | Required when route, security, schema, or RBAC is involved. | Optional unless reviewer policy marks it required. | Yes, after KB and validation gates pass. |
| `high` | Required. | Required. | Required. | Only after KB update, validation, and approval are complete. |
| `critical` | Required. | Required. | Required. | Only after explicit approval; commit should be blocked while any gate is incomplete. |

## Workflow Gates

### 1. Task Gate

Required inputs:

- `task_id`
- title
- task type
- domain
- priority
- risk level
- initial `affected_paths` or expected paths

Failure conditions:

- Missing `task_id`
- Missing domain
- Missing risk level
- Missing `affected_paths` or expected paths

### 2. Plan Gate

Required inputs:

- `plan_id`
- `task_id`
- steps
- expected changes
- KB impact
- risk assessment

Failure conditions:

- Plan not linked to task
- Missing KB impact assessment
- Missing risk assessment

### 3. KB Update Gate

Required inputs:

- Final or current `changed_paths`
- Manifest matching result
- Affected domains
- Affected KB files
- KB update report or skip reason

Failure conditions:

- KB update required but no report
- KB update skipped without reason
- Affected KB files not checked
- Review-required domain auto-updated without concrete evidence

### 4. Validation Gate

Required inputs:

- Risk level
- Validation report when required
- Reviewer status when approval is required

Failure conditions:

- High-risk task without validation report
- Security/route/schema task without validation report
- Required approval missing
- Reviewer status is `pending` or `changes_requested`

### 5. Commit Gate

Required inputs:

- Branch name with `task_id`
- Commit message with `task_id`
- KB update status
- KB update report path when required
- Validation report path when required
- Reviewer status when required

Failure conditions:

- Branch without `task_id`
- Commit without `task_id`
- Commit body missing KB update status
- KB update required but no report
- Validation required but no report
- Approval required but not approved

### 6. Close Gate

Required inputs:

- Linked commit hash
- Final task status
- KB update status
- Validation status when required
- Reviewer status when required

Failure conditions:

- Commit hash missing
- KB update incomplete
- Validation incomplete
- Reviewer approval missing
- Known KB drift remains unresolved

## Minimal Manual Workflow

1. Create task.
2. Create plan.
3. Identify `changed_paths`.
4. Run KB update if needed.
5. Run validation if needed.
6. Commit with `task_id`.
7. Close task.

Manual workflow notes:

- `changed_paths` may be supplied manually until automated diff extraction exists.
- KB update reports are created manually under `ai/domain-kb/updates/`.
- Reviewer status may be recorded manually until GitNexus or Oracle MCP integration exists.
- No MCP, script, hook, or CI implementation is required for the first policy phase.

## Failure Conditions

- Missing `task_id`.
- Branch without `task_id`.
- Commit without `task_id`.
- KB update required but no report.
- Validation required but no report.
- High-risk task without approval.
- `changed_paths` missing.
- Task domain missing.
- Risk level missing.
- KB update skipped without reason.
- Commit body missing KB update status.
- Review-required domain auto-updated without evidence.

## Final Summary

This policy prepares GitNexus for future automation by turning the validated KB update behavior into explicit workflow gates. It defines how task metadata, branch names, commit messages, KB update reports, validation reports, and reviewer status should connect before any automation is implemented.

The immediate value is manual consistency:

- every task has a traceable `task_id`;
- every changed path can be mapped to KB impact;
- every required KB update has a report;
- every high-risk task has validation and approval;
- every commit can prove whether KB drift was prevented or intentionally skipped.

Future automation can implement these rules incrementally through GitNexus, Oracle MCP review, git hooks, CI checks, and automated changed-path extraction.
