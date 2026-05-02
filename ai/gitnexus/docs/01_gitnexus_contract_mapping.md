# 01 GitNexus Contract Mapping

## Purpose

Map existing Domain KB contracts, Oracle MCP capabilities, project rules, and workflow policies into GitNexus-ready manual metadata rules.

This document is implementation-ready, but it is not implementation. It does not create schemas, automation, task examples, plan examples, validation examples, commit gate code, or Git behavior.

## Source Systems Mapped Into GitNexus

| Source System | Current Role | What GitNexus Consumes |
|---------------|--------------|-------------------------|
| Domain KB | Source of truth for domain ownership, KB update policy, route/security/schema baselines, GitNexus contract, and manifest mapping. | Domain names, KB impact rules, watched paths, KB update requirements, validation expectations. |
| Oracle MCP | Read-only repository evidence provider. | Evidence references from repo, route, model, controller, permission, and filter lookup tools. |
| Existing AI rules | Project safety and implementation constraints. | Scope rules, no surprise refactors, no uncontrolled file movement, no encoding corruption, no unsafe migrations. |
| Existing workflow policy | Task lifecycle, task type, risk, validation, KB update, approval, branch, commit, and gate rules. | Manual status model, reviewer status, validation requirement, KB update decision, commit readiness. |

## Field Mapping Table

| Source Concept | GitNexus Metadata Field | Rule | Required? |
|----------------|-------------------------|------|-----------|
| Request intent | `source_request` | Capture the original user/system request in concise form. | Yes |
| Domain names | `affected_domains` | Use Domain KB domain names; avoid inventing new domain labels without review. | Yes |
| Repo paths | `affected_files` | Record expected or actual changed/reviewed repository paths. | Yes |
| Route/security/schema impact | `validation_required` | Set to true for route, filter, RBAC, auth, schema, migration, model, or high-risk changes. | Yes |
| KB manifest match | `kb_update_required` | Set from `ai/domain-kb/kb-manifest.yaml` impact mapping and task type. | Yes |
| Oracle lookup results | `oracle_evidence` | Store tool name, query, evidence summary, source file, and line/path when available. | No, but required when ownership or risk is unclear |
| Approval need | `reviewer_status` | Use workflow policy statuses: `not_required`, `pending`, `approved`, `changes_requested`. | Yes |
| Final readiness | `commit_allowed` | True only when metadata, scope, validation, KB decision, and reviewer decision are complete. | Yes |
| Lifecycle states | `status` | Use the manual lifecycle contract states in this document. | Yes |

Additional recommended metadata from Domain KB baseline:

| Domain KB Field | GitNexus Field | Notes |
|-----------------|----------------|-------|
| `task_id` | `task_id` | Stable task identifier such as `GNX-0001`. |
| `task_title` | `task_title` | Human-readable title. |
| `task_type` | `task_type` | Must align with workflow policy task types. |
| `risk_level` | `risk_level` | `low`, `medium`, `high`, or `critical`. |
| `affected_kb_files` | `affected_kb_files` | Derived from manifest mapping when KB impact exists. |
| `plan_id` | `plan_id` | Optional until plan format is created. |
| `plan_file` | `plan_file` | Required after planning. |
| `validation_report` | `validation_file` | Required when validation is required. |
| `kb_update_status` | `kb_update_status` | `not_required`, `required`, `completed`, or `deferred`. |
| `branch_name` | `branch_name` | Required later when branch policy is active. |
| `commit_hash` | `commit_hash` | Empty until commit exists. |
| `created_at` | `created_at` | ISO 8601 timestamp. |
| `updated_at` | `updated_at` | ISO 8601 timestamp. |

## Oracle Evidence Contract

Oracle evidence must be source-anchored and minimal. GitNexus should store references, not uncontrolled raw dumps.

Recommended evidence object:

```text
tool: <oracle tool name>
query: <query used>
evidence_type: <repo_file|route|model|controller|permission|filter>
source_file: <relative path>
line_number: <line number when available>
summary: <short evidence summary>
confidence: <high|medium|low|needs_review>
```

### Tool Mapping

| Oracle Tool | When To Use | Evidence To Store | When Not Required |
|-------------|-------------|-------------------|-------------------|
| `repo_file_lookup` | When the exact file path is unknown or a task references a partial filename/path. | Matched relative path, extension/type, result count, query. | Not required when affected files are already exact and verified. |
| `route_lookup` | For route changes, route ownership, route/controller binding, route filters, admin/user/secretary access paths. | Route path, HTTP method, controller target, source file, line number. | Not required for changes with no route behavior or route ownership impact. |
| `model_lookup` | For model, migration, seeder, table, allowed field, return type, soft delete, UUID, or schema mapping questions. | Model class, table name, field, migration/seeder reference, source file, line number. | Not required for pure view/copy changes with no model/schema impact. |
| `controller_lookup` | For route handler tracing, controller method ownership, redirect/view/service/model references, or controller-level risk. | Controller class, method, related route, matched text, source file, line number. | Not required when task does not touch route/controller behavior. |
| `permission_lookup` | For RBAC, permission code, role, admin/secretary/user access, route permission, or auth service evidence. | Permission code, role, route/filter source, service/model reference, source file, line number. | Not required for tasks with no auth/RBAC/permission impact. |
| `filter_lookup` | For filter aliases, global filters, route filters, CSRF, role filters, permission filters, and filter class behavior. | Filter alias, route filter, global filter, role/permission expression, source file, line number. | Not required when task has no route/security/filter impact. |

Evidence rules:

- Do not invent evidence.
- Do not store secrets.
- Do not require broad discovery when exact paths are known.
- Mark uncertain evidence as `needs_review`.
- Prefer exact file/line evidence when available.

## Manual Lifecycle Contract

| State | Entry Condition | Exit Condition |
|-------|-----------------|----------------|
| `draft` | Request exists but metadata is incomplete. | Required task metadata is completed enough for planning. |
| `ready_for_plan` | Task has task id, title, type, source request, affected domains or expected paths, and risk level. | Plan is created or task is blocked. |
| `planned` | Plan exists and includes expected changes, KB impact, risk, and validation expectations. | Implementation starts. |
| `in_progress` | Work is actively being performed or reviewed. | Changed paths are known and validation/KB decisions can be made. |
| `validation_required` | Risk, route, security, schema, RBAC, or reviewer policy requires validation. | Validation report is linked or task becomes blocked. |
| `kb_update_required` | Affected paths match Domain KB impact rules or task type requires KB review. | KB update report is linked, or skip/defer reason is approved. |
| `ready_for_commit` | Metadata, scope, validation, KB decision, and reviewer gates are complete. | Commit is created and linked, or task returns to blocked/in_progress if new changes appear. |
| `committed` | Commit exists and is linked to task metadata. | Post-commit checks pass and task can close. |
| `closed` | Work, validation, KB decision, approval, and commit linkage are complete. | Reopen only for drift, regression, or explicit follow-up. |
| `blocked` | Required metadata, plan, validation, KB report, approval, or evidence is missing. | Blocking issue is resolved and task returns to the correct prior state. |

State rules:

- A task cannot skip from `draft` to `ready_for_commit`.
- `validation_required` and `kb_update_required` may both apply.
- `blocked` must include a reason.
- `closed` requires traceable evidence.

## Commit Readiness Contract

Manual commit can be allowed only if:

- task metadata is complete
- task scope is respected
- affected files are known
- affected domains are known
- Oracle evidence is attached when needed
- required validation is complete
- Domain KB update decision is complete
- reviewer decision is complete
- task is not in `blocked`
- `commit_allowed` is explicitly true

Commit must not be allowed if:

- task id is missing
- affected files are missing
- required validation report is missing
- required KB update report or skip reason is missing
- reviewer status is `pending` or `changes_requested`
- high/critical risk lacks approval
- task has unreviewed route/security/schema/RBAC impact
- source-of-truth files were moved without an approved archive/migration plan

## Safety Constraints

GitNexus must enforce these manual safety constraints:

- no broad discovery by default
- no invented evidence
- no automatic commits
- no automatic staging
- no Git reset, checkout, clean, or discard behavior
- no hidden file changes
- no source-of-truth movement
- no encoding corruption
- no mojibake introduction
- no uncontrolled refactors
- no `app/` modification by GitNexus itself
- no Domain KB modification unless a task explicitly requires KB update
- no schema creation until manual formats are validated

## Recommended Next File

Recommended next file:

```text
ai/gitnexus/docs/02_manual_task_metadata_format.md
```

Purpose:

- Define the manual task metadata format before schemas exist.
- Keep it documentation-only.
- Do not create task examples yet unless explicitly requested.
- Do not create JSON schemas yet.

## Pass / Warning / Blocker Summary

| Area | Status | Notes |
|------|--------|------|
| Domain KB contract mapping | Pass | Baseline metadata, workflow policy, and contract readiness are mapped to GitNexus fields. |
| Oracle evidence mapping | Pass | Six Oracle lookup tools are mapped to evidence types and usage rules. |
| Manual lifecycle contract | Pass | Safe manual states and transitions are defined. |
| Commit readiness contract | Pass | Manual commit gate conditions are defined without Git automation. |
| Schema readiness | Warning | Manual metadata format must be defined and validated before schemas are created. |
| Automation readiness | Warning | Automation remains intentionally out of scope. |
| Blockers | None | No blocker for the next documentation-only task metadata format file. |

## Final Decision

GitNexus Contract Mapping Complete: YES
