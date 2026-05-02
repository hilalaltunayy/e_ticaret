# 02 GitNexus Manual Task Format

## Purpose

Define the first manual GitNexus task file format.

This document defines how future task files should be written before schemas, automation, task generators, plan examples, validation examples, or commit gate implementation exist.

## 1. Task File Naming Convention

Future folder:

```text
ai/gitnexus/tasks/
```

Filename pattern:

```text
GNX-0001_short_slug.md
```

Task id pattern:

```text
GNX-0001
```

Slug rules:

- lowercase
- ASCII only
- words separated with `_`
- concise and stable
- based on task title
- no spaces
- no Turkish characters
- no punctuation except `_`
- do not rename after task creation unless explicitly approved

Examples of valid filename shape:

```text
GNX-0001_route_baseline_review.md
GNX-0002_product_schema_audit.md
```

These are naming examples only, not actual task files.

## 2. Required Task Metadata Fields

| Field | Required | Allowed / Expected Value | Notes |
|------|----------|--------------------------|------|
| `task_id` | Yes | `GNX-0001` pattern | Stable identifier. |
| `task_title` | Yes | short human-readable title | Must match task intent. |
| `task_type` | Yes | allowed task type list below | Controls validation and KB expectations. |
| `source_request` | Yes | concise source request summary | Do not paste secrets. |
| `created_at` | Yes | ISO 8601 timestamp | Example: `2026-05-01T12:00:00+03:00`. |
| `status` | Yes | allowed status list below | Manual lifecycle state. |
| `affected_domains` | Yes | Domain KB domain names | Use existing Domain KB names. |
| `affected_files_expected` | Yes | relative paths or `TBD` | Expected paths before implementation. |
| `affected_files_actual` | Yes | relative paths or empty list | Filled after implementation. |
| `oracle_evidence` | Yes | evidence list or `not_required` | Required when uncertainty or risk rules apply. |
| `plan_file` | Yes after planning | relative path or `pending` | Links future plan file. |
| `validation_required` | Yes | `true` / `false` | Must follow validation rules below. |
| `validation_file` | Yes when required | relative path or `pending` | Required before commit readiness. |
| `kb_update_required` | Yes | `true` / `false` | Must follow KB review rules below. |
| `kb_update_file` | Yes when required | relative path or `pending` | Required before commit readiness. |
| `reviewer_status` | Yes | `not_required`, `pending`, `approved`, `changes_requested` | Must be complete before commit readiness. |
| `commit_allowed` | Yes | `true` / `false` | True only under commit readiness rules. |
| `blockers` | Yes | list or `none` | Must be empty/none before commit readiness. |

## 3. Allowed `task_type` Values

| Task Type | Meaning | Validation Bias |
|-----------|---------|-----------------|
| `feature` | Adds or changes user-visible/domain behavior. | Depends on affected files and risk. |
| `fix` | Corrects broken or incorrect behavior. | Depends on affected files and risk. |
| `docs` | Documentation-only change. | Usually low unless policy/source-of-truth docs change. |
| `refactor` | Code restructuring without intended behavior change. | Needs review if broad or high-risk. |
| `chore` | Maintenance work. | Depends on affected files. |
| `security` | Auth, RBAC, filter, session, CSRF, or sensitive access work. | Always validation required. |
| `route` | Route definitions, route groups, route filters, or controller bindings. | Always validation required. |
| `schema` | Models, migrations, seeders, or database mapping. | Always validation required. |
| `ui` | Views, layout, styling, copy, or frontend interaction. | Escalate if backend-flow impact exists. |
| `kb_update` | Domain KB or KB report work. | Requires KB policy awareness. |

## 4. Allowed `status` Values

| Status | Meaning |
|--------|---------|
| `draft` | Task exists but metadata is incomplete. |
| `ready_for_plan` | Task has enough metadata to create a plan. |
| `planned` | Plan exists and is linked. |
| `in_progress` | Work or review is active. |
| `validation_required` | Validation is required and not complete. |
| `kb_update_required` | KB review/update is required and not complete. |
| `ready_for_commit` | All required gates are complete. |
| `committed` | Commit exists and is linked. |
| `closed` | Task is complete. |
| `blocked` | Task cannot proceed until blockers are resolved. |

## 5. Required Markdown Sections For Future Task Files

Future task files must include these sections:

1. Header
2. Source Request
3. Scope
4. Affected Domains
5. Expected Files
6. Oracle Evidence
7. Plan Link
8. Validation Requirement
9. KB Update Requirement
10. Commit Gate
11. Blockers
12. Notes

## 6. Rules For When Oracle Evidence Is Required

Oracle evidence is required when:

- route ownership is uncertain
- controller ownership is uncertain
- model ownership is uncertain
- permission impact exists or is unclear
- filter impact exists or is unclear
- schema/model impact exists or is unclear
- a high-risk refactor is proposed
- a task crosses multiple domains
- affected files are not exact
- Domain KB and request intent disagree

Oracle evidence is not required when:

- the task is documentation-only and source files are already exact
- the affected files and domain ownership are already clear
- a validation report explicitly states Oracle evidence is not needed

## 7. Rules For When Validation Is Required

Validation is required for:

- auth, RBAC, or filter changes
- route changes
- schema, migration, model, or seeder changes
- payment, order, cart, or checkout changes
- security-sensitive changes
- builder or publish-flow changes
- cross-domain changes
- high-risk or critical-risk tasks
- any task where reviewer policy requires validation

Validation may be skipped only when:

- the task is low-risk
- the task is documentation-only or non-behavioral
- the skip reason is recorded
- reviewer status permits the skip

## 8. Rules For When KB Update Review Is Required

KB update review is required when:

- affected files match `ai/domain-kb/kb-manifest.yaml`
- route baseline or route matrix changes
- security/filter findings change
- schema/model baseline changes
- architecture, rules, current state, or decisions change
- new domain behavior is added
- permission matrix changes
- claim registry facts change
- task adds, removes, or changes domain ownership

KB update review may be skipped only when:

- affected paths do not map to KB impact
- the change is proven documentation-only outside Domain KB
- the change is pure formatting with no source-of-truth impact
- the skip reason is recorded

## 9. Commit Readiness Rules

`commit_allowed` can be `true` only if:

- required metadata is complete
- scope is respected
- `plan_file` exists when needed
- validation is complete or explicitly skipped with reason
- KB update is complete or explicitly skipped with reason
- `reviewer_status` is `approved` or `not_required`
- `blockers` is empty or `none`
- `status` is `ready_for_commit`

`commit_allowed` must be `false` if:

- task metadata is incomplete
- affected files are unknown
- affected domains are unknown
- Oracle evidence is required but missing
- validation is required but missing
- KB update review is required but missing
- reviewer status is `pending` or `changes_requested`
- blockers are present
- status is not `ready_for_commit`

## 10. Minimal Blank Task Template

Use this template inside future task files. Do not create actual task files until explicitly requested.

```markdown
# GNX-0000 Task Title

## Header

| Field | Value |
|------|-------|
| task_id | GNX-0000 |
| task_title |  |
| task_type |  |
| source_request |  |
| created_at |  |
| status | draft |
| affected_domains |  |
| affected_files_expected |  |
| affected_files_actual |  |
| oracle_evidence | not_required |
| plan_file | pending |
| validation_required | false |
| validation_file | not_required |
| kb_update_required | false |
| kb_update_file | not_required |
| reviewer_status | pending |
| commit_allowed | false |
| blockers | none |

## Source Request


## Scope


## Affected Domains


## Expected Files


## Oracle Evidence


## Plan Link


## Validation Requirement


## KB Update Requirement


## Commit Gate


## Blockers


## Notes

```

## 11. Recommended Next File

Recommended next file:

```text
ai/gitnexus/docs/03_gitnexus_manual_plan_format.md
```

Purpose:

- Define the manual plan file format.
- Keep it documentation-only.
- Do not create plan examples yet unless explicitly requested.
- Do not create schemas yet.

## 12. Pass / Warning / Blocker Summary

| Area | Status | Notes |
|------|--------|------|
| Task naming convention | Pass | Future task folder, file pattern, task id pattern, and slug rules are defined. |
| Required metadata | Pass | Manual task metadata fields are defined. |
| Task type values | Pass | Values match GitNexus workflow policy. |
| Status values | Pass | Values match manual lifecycle contract. |
| Oracle evidence rules | Pass | Evidence required conditions are defined. |
| Validation rules | Pass | Security, route, schema, order/payment/cart/checkout, builder, and cross-domain triggers are defined. |
| KB update rules | Pass | Manifest, route/security/schema, architecture/rules/current-state/decision, domain behavior, and permission matrix triggers are defined. |
| Commit readiness | Pass | Manual `commit_allowed` conditions are explicit. |
| Schemas | Warning | Not created yet by design. |
| Actual task examples | Warning | Not created yet by design. |
| Blockers | None | No blocker for the next documentation-only manual plan format file. |

## Final Decision

GitNexus Manual Task Format Complete: YES
