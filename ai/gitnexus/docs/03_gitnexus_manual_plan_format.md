# 03 GitNexus Manual Plan Format

## Purpose

Define the first manual GitNexus plan file format linked to GitNexus tasks.

This document defines how future plan files should be written before schemas, automation, actual plan files, task examples, validation examples, or commit gate implementation exist.

## 1. Future Plan Folder And Naming Convention

Future folder:

```text
ai/gitnexus/plans/
```

Filename pattern:

```text
GNX-0001_execution_plan.md
```

Linked task id rules:

- Every plan must link to exactly one primary `task_id`.
- The `task_id` must match an existing future task file.
- The filename must begin with the linked `task_id`.
- A plan must not be created for an undefined task.

One active plan rule:

- One task can have one active plan.
- The active plan is the plan with `plan_status` set to `approved` or `executing`.
- If a new plan replaces an old plan, the old plan must be marked `replaced`.

Revision handling rules:

- Minor clarification can be edited in the same plan while status is `draft`.
- After approval, significant scope changes require a new revision.
- Suggested future revision filename:

```text
GNX-0001_execution_plan_r2.md
```

- The replaced plan must link to the replacement plan.
- The replacement plan must explain why the previous plan was replaced.

## 2. Required Plan Metadata Fields

| Field | Required | Allowed / Expected Value | Notes |
|------|----------|--------------------------|------|
| `task_id` | Yes | `GNX-0001` pattern | Must link to one task. |
| `plan_title` | Yes | short human-readable title | Should describe the execution approach. |
| `created_at` | Yes | ISO 8601 timestamp | Example: `2026-05-01T12:00:00+03:00`. |
| `plan_status` | Yes | allowed plan status list below | Manual plan lifecycle state. |
| `linked_task_file` | Yes | relative path or `pending` | Must point to future task file before approval. |
| `owner_scope` | Yes | docs / app / kb / oracle / gitnexus / mixed | Defines ownership boundary. |
| `risk_level` | Yes | `low`, `medium`, `high`, `critical` | Must follow risk rules below. |
| `requires_oracle_evidence` | Yes | `true` / `false` | True when uncertainty/risk rules apply. |
| `requires_validation` | Yes | `true` / `false` | True when validation rules apply. |
| `requires_kb_review` | Yes | `true` / `false` | True when KB review rules apply. |
| `estimated_change_scope` | Yes | `tiny`, `small`, `medium`, `large`, `cross_domain` | Must follow change scope rules below. |
| `rollback_complexity` | Yes | `none`, `low`, `medium`, `high`, `critical` | Manual assessment. |
| `notes` | Yes | text or `none` | Important planning notes. |

## 3. Allowed `plan_status` Values

| Status | Meaning |
|--------|---------|
| `draft` | Plan exists but is not ready for review. |
| `ready_for_review` | Plan has enough detail for review. |
| `approved` | Plan is accepted and can be executed. |
| `executing` | Work is being performed from this plan. |
| `paused` | Execution is temporarily stopped. |
| `replaced` | Plan was superseded by another plan. |
| `completed` | Plan execution is complete. |
| `blocked` | Plan cannot proceed until blockers are resolved. |

## 4. Required Markdown Sections For Future Plan Files

Future plan files must include these sections:

1. Header
2. Linked Task
3. Objective
4. Scope In
5. Scope Out
6. Affected Files Expected
7. Oracle Evidence Needed
8. Implementation Steps
9. Validation Steps
10. KB Review Impact
11. Risks
12. Rollback Notes
13. Approval Status
14. Notes

## 5. Risk Level Rules

| Risk Level | Meaning | Examples |
|------------|---------|----------|
| `low` | Documentation-only, text-only, or isolated UI/copy changes with no behavior impact. | text/UI only |
| `medium` | Single-domain route/controller/service/view/model behavior with limited blast radius. | route/controller/service single domain |
| `high` | Security, RBAC, auth, order, payment, cart, checkout, schema, migration, or multi-step business flow changes. | RBAC/auth/order/payment/schema |
| `critical` | Destructive migration, security incident, live outage, data loss risk, or production-impacting access failure. | destructive migration/security incident/live outage |

Risk rules:

- `security`, `route`, and `schema` task types should start at least `high` unless proven lower by review.
- Cross-domain changes should not be lower than `medium`.
- Payment/order/cart/checkout changes should not be lower than `high` unless documentation-only.
- Destructive database operations must be treated as `critical`.

## 6. Estimated Change Scope Rules

| Scope | Meaning |
|-------|---------|
| `tiny` | One small documentation or metadata change. |
| `small` | One or two files in one domain with limited behavior impact. |
| `medium` | Several files in one domain or a normal controller/service/model/view path. |
| `large` | Many files, broad UI changes, or significant module behavior. |
| `cross_domain` | Multiple domains or architecture/security/schema boundaries are affected. |

Scope rules:

- Use `cross_domain` when affected domains are unclear or multiple domains are expected.
- Do not classify route/security/schema work as `tiny` unless documentation-only.
- Use `large` or `cross_domain` for broad refactors.

## 7. Rules For Requiring Oracle Evidence

Oracle evidence is required when:

- ownership is unclear
- route ownership or route binding is unclear
- controller ownership is unclear
- model ownership is unclear
- permission impact exists or is unclear
- filter impact exists or is unclear
- schema/model impact exists or is unclear
- a high-risk refactor is proposed
- a cross-domain change is expected
- affected files are not exact
- Domain KB and request intent disagree

Oracle evidence may be skipped when:

- the task is documentation-only
- affected files are exact
- domain ownership is already clear
- the skip reason is documented

## 8. Rules For Requiring Validation Steps

Validation steps are required when:

- auth, RBAC, filter, or CSRF behavior is affected
- route definitions or route filters are affected
- controllers, services, models, migrations, or seeders are affected
- payment, order, cart, or checkout behavior is affected
- builder publish/draft behavior is affected
- security-sensitive behavior is affected
- cross-domain behavior is affected
- risk level is `high` or `critical`

Validation steps may be skipped only when:

- risk is `low`
- task is documentation-only
- no runtime behavior changes
- skip reason is documented

## 9. Rules For Requiring KB Review

KB review is required when:

- affected files match `ai/domain-kb/kb-manifest.yaml`
- route/security/schema baseline changes
- permission matrix changes
- architecture, rules, current state, or decisions change
- new domain behavior is added
- existing domain ownership changes
- Oracle evidence introduces automation-relevant findings

KB review may be skipped only when:

- affected files do not map to KB impact
- change is documentation-only outside Domain KB
- change is formatting-only with no source-of-truth impact
- skip reason is documented

## 10. Approval Contract

Plan can become `approved` only if:

- linked task exists
- linked task id matches plan filename
- scope is clear
- scope out is clear
- expected affected files are listed or explicitly marked `TBD`
- risks are documented
- validation expectation is defined
- KB impact is decided
- Oracle evidence requirement is decided
- rollback notes are present
- blockers are empty or `none`

Plan must remain `blocked` or `draft` if:

- linked task is missing
- task id mismatch exists
- scope is ambiguous
- risk is undocumented
- validation requirement is unclear
- KB review requirement is unclear
- blockers exist

## 11. Minimal Blank Plan Template

Use this template inside future plan files. Do not create actual plan files until explicitly requested.

```markdown
# GNX-0000 Execution Plan

## Header

| Field | Value |
|------|-------|
| task_id | GNX-0000 |
| plan_title |  |
| created_at |  |
| plan_status | draft |
| linked_task_file | pending |
| owner_scope |  |
| risk_level |  |
| requires_oracle_evidence | false |
| requires_validation | false |
| requires_kb_review | false |
| estimated_change_scope |  |
| rollback_complexity |  |
| notes | none |

## Linked Task


## Objective


## Scope In


## Scope Out


## Affected Files Expected


## Oracle Evidence Needed


## Implementation Steps


## Validation Steps


## KB Review Impact


## Risks


## Rollback Notes


## Approval Status


## Notes

```

## 12. Recommended Next File

Recommended next file:

```text
ai/gitnexus/docs/04_gitnexus_manual_validation_format.md
```

Purpose:

- Define the manual validation report format.
- Keep it documentation-only.
- Do not create validation examples yet unless explicitly requested.
- Do not create schemas yet.

## 13. Pass / Warning / Blocker Summary

| Area | Status | Notes |
|------|--------|------|
| Plan naming convention | Pass | Future folder, filename pattern, task link, active plan, and revision rules are defined. |
| Required metadata | Pass | Manual plan metadata fields are defined. |
| Plan status values | Pass | Manual plan lifecycle values are defined. |
| Required sections | Pass | Future plan Markdown sections are defined. |
| Risk rules | Pass | Low, medium, high, and critical rules are defined. |
| Change scope rules | Pass | Tiny, small, medium, large, and cross-domain rules are defined. |
| Oracle evidence rules | Pass | Required conditions are aligned with task format and Oracle capability. |
| Validation rules | Pass | Security, route, schema, order/payment/cart/checkout, builder, and cross-domain triggers are defined. |
| KB review rules | Pass | Manifest and source-of-truth impact rules are defined. |
| Approval contract | Pass | Plan approval requirements are explicit. |
| Schemas | Warning | Not created yet by design. |
| Actual plan files | Warning | Not created yet by design. |
| Blockers | None | No blocker for the next documentation-only validation format file. |

## Final Decision

GitNexus Manual Plan Format Complete: YES
