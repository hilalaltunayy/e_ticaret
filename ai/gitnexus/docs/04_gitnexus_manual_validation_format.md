# 04 GitNexus Manual Validation Format

## Purpose

Define the first manual GitNexus validation file format linked to tasks and plans.

This document defines how future validation files should be written before schemas, automation, actual validation files, task examples, plan examples, or commit gate implementation exist.

## 1. Future Validation Folder And Naming Convention

Future folder:

```text
ai/gitnexus/validations/
```

Filename pattern:

```text
GNX-0001_validation_report.md
```

Linked task id rules:

- Every validation report must link to exactly one primary `task_id`.
- The `task_id` must match an existing future task file.
- The filename must begin with the linked `task_id`.
- A validation report must not be created for an undefined task.

Linked plan rules:

- A validation report should link to the active plan when a plan exists.
- If validation is performed without a plan, the report must explain why.
- If the linked plan is replaced, future validation should point to the active replacement plan.

Rerun / revalidation revision rules:

- Failed tasks can rerun validation.
- Revalidation reports should use revision suffixes:

```text
GNX-0001_validation_report_r2.md
GNX-0001_validation_report_r3.md
```

- Superseded reports remain historical evidence.
- The latest non-superseded report is the active validation report.
- A superseded report must reference the newer active report when possible.

## 2. Required Validation Metadata Fields

| Field | Required | Allowed / Expected Value | Notes |
|------|----------|--------------------------|------|
| `task_id` | Yes | `GNX-0001` pattern | Must link to one task. |
| `validation_title` | Yes | short human-readable title | Should describe what was validated. |
| `created_at` | Yes | ISO 8601 timestamp | Example: `2026-05-01T12:00:00+03:00`. |
| `validation_status` | Yes | allowed validation status list below | Manual validation lifecycle state. |
| `linked_task_file` | Yes | relative path or `pending` | Must point to future task file before validation can pass. |
| `linked_plan_file` | Yes when plan exists | relative path, `not_required`, or `pending` | Must point to active plan when applicable. |
| `validator_type` | Yes | allowed validator type list below | Defines validation method. |
| `validation_scope` | Yes | `tiny`, `small`, `medium`, `large`, `cross_domain` | Must follow scope rules below. |
| `environment` | Yes | local / docker / browser / static / manual / mixed | Describe validation environment. |
| `defects_found` | Yes | list or `none` | Include severity when defects exist. |
| `blocker_found` | Yes | `true` / `false` | True if validation blocks progress. |
| `kb_review_checked` | Yes | `true` / `false` | Whether KB impact was checked. |
| `commit_recommendation` | Yes | allowed recommendation list below | Manual commit gate input. |
| `notes` | Yes | text or `none` | Important validation notes. |

## 3. Allowed `validation_status` Values

| Status | Meaning |
|--------|---------|
| `draft` | Validation report exists but is incomplete. |
| `running` | Validation is in progress. |
| `passed` | Validation passed without meaningful notes. |
| `passed_with_notes` | Validation passed with non-blocking notes. |
| `failed` | Validation found defects that must be fixed. |
| `blocked` | Validation cannot complete because a required input or environment is missing. |
| `superseded` | Validation report was replaced by a newer report. |

## 4. Allowed `validator_type` Values

| Validator Type | Meaning |
|----------------|---------|
| `manual` | Human/manual validation. |
| `ai_review` | AI-assisted review without running code. |
| `local_runtime` | Local command/runtime validation. |
| `docker_runtime` | Docker-based runtime validation. |
| `static_review` | Static source/document inspection. |
| `security_review` | Security, auth, RBAC, filter, or secret-safety review. |
| `route_review` | Route, route filter, or route/controller binding review. |
| `schema_review` | Model, migration, seeder, or schema mapping review. |
| `ui_review` | Browser, visual, or user interface validation. |

## 5. Required Markdown Sections For Future Validation Files

Future validation files must include these sections:

1. Header
2. Linked Task
3. Linked Plan
4. Objective
5. Validation Scope
6. Environment
7. Checks Performed
8. Results
9. Defects Found
10. Blockers
11. KB Review Check
12. Commit Recommendation
13. Notes

## 6. Validation Scope Rules

| Scope | Meaning |
|-------|---------|
| `tiny` | One small documentation or metadata check. |
| `small` | One or two files or one isolated behavior check. |
| `medium` | Several files in one domain or normal route/controller/service/model/view validation. |
| `large` | Many files, broad module behavior, or high-risk flow validation. |
| `cross_domain` | Multiple domains or architecture/security/schema boundaries are validated. |

Scope rules:

- Use `cross_domain` when validation covers multiple domains.
- Do not classify security, route, or schema validation as `tiny` unless documentation-only.
- Use `large` or `cross_domain` for broad refactors.

## 7. When Validation Is Mandatory

Validation is mandatory for:

- auth, RBAC, or filter changes
- route changes
- controller or service logic changes
- schema, model, migration, or seeder changes
- order, cart, payment, or checkout changes
- security-sensitive changes
- builder publish/render changes
- cross-domain impact
- production-risk fixes
- high-risk or critical-risk tasks

Validation may be skipped only when:

- risk is low
- the task is documentation-only
- no runtime behavior changes
- skip reason is documented in the task and plan
- reviewer status allows the skip

## 8. Commit Recommendation Rules

Allowed values:

| Recommendation | Meaning |
|----------------|---------|
| `approve` | Validation passed and commit may proceed if other gates are complete. |
| `approve_with_notes` | Commit may proceed, but non-blocking notes should be tracked. |
| `reject` | Commit should not proceed because validation failed. |
| `needs_rework` | Work should return to implementation before another validation. |
| `blocked` | Validation could not complete; commit must not proceed. |

Recommendation rules:

- Use `approve` only when no blockers or meaningful defects remain.
- Use `approve_with_notes` for non-blocking issues.
- Use `reject` when defects invalidate the change.
- Use `needs_rework` when implementation changes are required.
- Use `blocked` when validation cannot be completed.

## 9. Defect Severity Guidance

| Severity | Meaning |
|----------|---------|
| `minor` | Small issue that does not block commit. |
| `moderate` | Issue should be fixed soon but may not block if approved. |
| `major` | Issue blocks commit unless explicitly waived. |
| `critical` | Issue blocks commit and requires immediate correction. |

Severity rules:

- Security, permission, data integrity, migration, payment, order, or checkout defects should usually be `major` or `critical`.
- UI polish defects are usually `minor` or `moderate` unless they block core flow.
- Any defect that can cause data loss, unauthorized access, broken checkout, or production outage is `critical`.

## 10. Revalidation Rules

- Failed tasks can rerun validation.
- Revalidation must create a new validation report unless the original report is still `draft`.
- Superseded reports remain historical evidence.
- Latest non-superseded report is active.
- A new validation report must reference the previous failed or superseded report when applicable.
- Task commit readiness must use the latest active validation report only.
- If new affected files appear after validation, validation must be re-evaluated.

## 11. Minimal Blank Validation Template

Use this template inside future validation files. Do not create actual validation files until explicitly requested.

```markdown
# GNX-0000 Validation Report

## Header

| Field | Value |
|------|-------|
| task_id | GNX-0000 |
| validation_title |  |
| created_at |  |
| validation_status | draft |
| linked_task_file | pending |
| linked_plan_file | pending |
| validator_type |  |
| validation_scope |  |
| environment |  |
| defects_found | none |
| blocker_found | false |
| kb_review_checked | false |
| commit_recommendation | blocked |
| notes | none |

## Linked Task


## Linked Plan


## Objective


## Validation Scope


## Environment


## Checks Performed


## Results


## Defects Found


## Blockers


## KB Review Check


## Commit Recommendation


## Notes

```

## 12. Recommended Next File

Recommended next file:

```text
ai/gitnexus/docs/05_gitnexus_manual_commit_gate_format.md
```

Purpose:

- Define the manual commit gate decision format.
- Keep it documentation-only.
- Do not create commit gate implementation yet.
- Do not create schemas yet.

## 13. Pass / Warning / Blocker Summary

| Area | Status | Notes |
|------|--------|------|
| Validation naming convention | Pass | Future folder, filename pattern, task link, plan link, and revision rules are defined. |
| Required metadata | Pass | Manual validation metadata fields are defined. |
| Validation status values | Pass | Manual validation lifecycle values are defined. |
| Validator types | Pass | Manual, AI, local, Docker, static, security, route, schema, and UI review types are defined. |
| Required sections | Pass | Future validation Markdown sections are defined. |
| Scope rules | Pass | Tiny, small, medium, large, and cross-domain rules are defined. |
| Mandatory validation rules | Pass | Auth/RBAC/filter, route, controller/service, schema/model, order/cart/payment/checkout, security, builder, cross-domain, and production-risk triggers are defined. |
| Commit recommendation rules | Pass | Manual recommendation values are defined. |
| Defect severity guidance | Pass | Minor, moderate, major, and critical severity rules are defined. |
| Revalidation rules | Pass | Failed, superseded, and active report rules are defined. |
| Schemas | Warning | Not created yet by design. |
| Actual validation files | Warning | Not created yet by design. |
| Blockers | None | No blocker for the next documentation-only commit gate format file. |

## Final Decision

GitNexus Manual Validation Format Complete: YES
