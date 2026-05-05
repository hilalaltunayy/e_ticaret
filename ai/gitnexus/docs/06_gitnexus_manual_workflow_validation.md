# 06 GitNexus Manual Workflow Validation

## Purpose

Validate the manual GitNexus workflow with a documentation-only dry-run scenario.

This file checks whether the manual task, plan, validation, and commit gate formats can represent a realistic feature request without creating schemas, automation, actual records, code changes, Git operations, Docker runs, or Domain KB changes.

Dry-run scenario:

```text
Admin orders page should support filtering orders by date range.
```

## 1. Dry-Run Scope

| Scope Item | Decision |
|-----------|----------|
| documentation-only | yes |
| real code change | no |
| real task file creation | no |
| real plan file creation | no |
| real validation report creation | no |
| real commit gate execution | no |
| Docker execution | no |
| Git command execution | no |
| schema creation | no |
| automation creation | no |
| Domain KB modification | no |
| `app/` modification | no |

This document simulates metadata decisions only. It is not task evidence, plan evidence, validation evidence, or commit approval.

## 2. Simulated Task Metadata

| Field | Simulated Value |
|------|-----------------|
| `task_id` | `GNX-DRYRUN-0001` |
| `task_type` | `feature` |
| `source_request` | Admin orders page should support filtering orders by date range. |
| `affected_domains` | `Order`, `Admin Panel`; `Secretary Access` needs review if secretary order access uses the same page. |
| `affected_files_expected` | `app/Controllers/Admin/Orders.php`, `app/Services/OrdersService.php` or `app/Services/OrdersReportingService.php`, `app/Models/OrderModel.php`, `app/Views/admin/orders/**` |
| `oracle_evidence_required` | `true` |
| `validation_required` | `true` |
| `kb_update_required` decision | `true`, because expected Order/Admin order paths match `kb-manifest.yaml` and the change affects admin-facing order workflow behavior. |
| `initial_status` | `ready_for_plan` in simulation; no real task status is created. |

Task decision notes:

- `feature` is appropriate because the request adds user-visible admin workflow behavior.
- Order domain is primary because the change affects order listing/filtering.
- Admin Panel is secondary because the UI surface is an admin page.
- Oracle evidence should confirm the active controller, route, permission/filter context, and model/service query ownership before implementation.
- Validation is required because order/admin behavior is affected.
- KB update must be evaluated because expected files match manifest watched paths.

## 3. Simulated Plan Decision

| Field | Simulated Decision |
|------|--------------------|
| objective | Add date range filtering to the admin orders list while preserving existing order list behavior, permissions, pagination, and status filters. |
| scope in | Request input handling for start/end dates; service/model query filtering; admin orders view controls; validation of date input; preservation of existing filters. |
| scope out | No route rename; no permission change; no schema change; no migration; no redesign of admin orders page; no checkout/order creation change; no broad refactor. |
| expected files | `app/Controllers/Admin/Orders.php`, order service/reporting service, `app/Models/OrderModel.php` if query helper is needed, `app/Views/admin/orders/**`. |
| risk level | `medium`; escalate to `high` if filtering affects permissions, order totals, export/reporting, or shared order query behavior. |
| implementation steps summary | Identify active admin orders route/controller; add validated date inputs; pass filter DTO/array to service; apply bounded date query; preserve pagination and existing query parameters; update view controls minimally. |
| validation steps summary | Check empty filter, valid range, reversed/invalid range, one-sided range if supported, pagination retention, existing filters, permission-protected access, and no regression to order list defaults. |
| KB review impact | Required evaluation. Likely affected KB files include Order domain KB files and possibly Admin Panel route/security files if route/filter behavior changes. |

Plan decision notes:

- The plan format supports objective, scope in/out, expected files, risk, validation, KB review, and rollback notes.
- Oracle evidence is useful before implementation to avoid touching legacy or inactive admin order files.
- The approved plan must keep changes small and task-scoped under `ai/rules.md`.

## 4. Simulated Validation Decision

| Field | Simulated Decision |
|------|--------------------|
| `validator_type` | `manual`, `ai_review`, `route_review`, and possibly `local_runtime` if real implementation happens later. |
| `validation_scope` | `medium` |
| required checks | Controller receives date range safely; service/model applies correct range; invalid dates are rejected or ignored safely; default order list remains unchanged; pagination keeps filters; admin/secretary permissions remain intact; no schema or route drift unless explicitly planned. |
| expected pass criteria | Existing order listing works without filters; valid date range returns only matching orders; invalid input does not break page; permission boundaries remain unchanged; no unrelated UI redesign; KB update decision is recorded. |
| defects/blockers handling | Major or critical defects block commit; minor UI defects can become `passed_with_notes` only with reviewer approval; missing route/controller evidence blocks validation. |

Validation decision notes:

- The validation format can represent static review, manual review, route/security review, and runtime validation if later allowed.
- Because this dry-run creates no real implementation, no validation can actually pass here.
- A real future validation report would need source-anchored checks and a final `commit_recommendation`.

## 5. Simulated Commit Gate Decision

| Gate Question | Simulated Answer |
|--------------|------------------|
| task complete? | No real task exists; simulated metadata is representable. |
| plan approved? | No real plan exists; simulated plan decision is representable. |
| validation passed? | No real validation exists; cannot pass in this dry-run. |
| KB update evaluated? | Simulated evaluation says required; no real KB update report exists. |
| reviewer status? | Simulated status would be `pending` until real review. |
| blockers? | Yes for real commit: no actual task, plan, validation, KB update report, or reviewer approval exists. |
| final commit decision | `blocked` for any real commit; `allow_commit` is not available in a documentation-only dry-run. |

Commit gate decision notes:

- The commit gate format correctly blocks commit readiness when required evidence is only simulated.
- A future real commit could proceed only after task, approved plan, passed validation, completed or approved-skipped KB update, acceptable reviewer status, and empty blockers.
- No Git command is required or allowed by this validation.

## 6. Workflow Consistency Check

| Check | Result | Notes |
|------|--------|------|
| task format supports the scenario | Pass | Required fields can capture feature type, source request, domains, expected files, validation, KB decision, reviewer status, and blockers. |
| plan format supports the scenario | Pass | Objective, scope, expected files, risk, Oracle evidence, validation steps, and KB review impact are representable. |
| validation format supports the scenario | Pass | Validator type, validation scope, checks, defects, blockers, KB review, and commit recommendation are representable. |
| commit gate format supports the scenario | Pass | It correctly requires linked task, approved plan, active validation, KB decision, reviewer status, and no blockers. |
| KB update decision can be represented | Pass | Manifest-based impact can be recorded as required, completed, skipped with reason, or blocked. |
| Oracle evidence can be represented | Pass | `route_lookup`, `controller_lookup`, `permission_lookup`, `filter_lookup`, and `model_lookup` can be referenced without storing uncontrolled raw dumps. |

## 7. Gaps Found

| Gap Area | Finding | Severity |
|---------|---------|----------|
| Dry-run task id shape | Existing task id examples use `GNX-0001`; this dry-run uses `GNX-DRYRUN-0001`. Future docs or schemas should decide whether dry-run ids are allowed. | Warning |
| Affected KB files | Task and commit gate formats can point to KB update files, but an explicit `affected_kb_files` field may be useful before schemas. | Warning |
| KB status vocabulary | Task format uses `kb_update_required` and `kb_update_file`; policy also uses `kb_update_status`. Future alignment should avoid duplicate status concepts. | Warning |
| Reviewer rules | `not_required` is allowed, but examples should clarify when medium-risk admin/order work may use it. | Warning |
| Oracle evidence timing | Formats support evidence references, but do not yet define whether Oracle evidence belongs before plan approval, before validation, or both. | Warning |
| Premature automation risk | The workflow is easy to automate too early. Manual validation should remain the source of truth until schemas and examples are reviewed. | Warning |
| Missing blockers | None blocking for manual documentation workflow. | Pass |

## 8. Pass / Warning / Blocker Summary

| Area | Status | Notes |
|------|--------|------|
| Documentation-only dry-run boundary | Pass | No real records, code changes, schemas, automation, Docker, Git, app, or Domain KB changes are required. |
| Simulated task metadata | Pass | Scenario can be represented with current task format. |
| Simulated plan decision | Pass | Scope, risk, expected files, validation, and KB impact are representable. |
| Simulated validation decision | Pass | Required checks and pass/block criteria are representable. |
| Simulated commit gate decision | Pass | Real commit is correctly blocked because evidence is simulated only. |
| KB update handling | Pass | Manifest-based Order/Admin impact can be evaluated and represented. |
| Oracle evidence handling | Pass | Read-only Oracle evidence references fit the workflow. |
| Format alignment | Warning | KB status naming and dry-run task id shape need later normalization. |
| Automation readiness | Warning | Still intentionally not ready for automation. |
| Schema readiness | Warning | Manual formats should be validated further before schemas. |
| Blockers | None | No blocker for the next documentation-only milestone. |

## 9. Recommendation For Next Milestone

Recommended next milestone:

```text
GitNexus manual format alignment review
```

Purpose:

- Align task, plan, validation, and commit gate metadata names before schemas.
- Decide whether dry-run ids are allowed.
- Normalize KB status fields.
- Define where Oracle evidence is required in the lifecycle.
- Keep the review documentation-only.
- Do not create schemas, automation, or actual workflow records yet.

## Final Decision

GitNexus Manual Workflow Validation Complete: YES
