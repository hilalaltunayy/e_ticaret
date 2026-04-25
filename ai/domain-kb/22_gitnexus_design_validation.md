# 22 GitNexus Design Validation

## Purpose

Validate whether the GitNexus system design is complete, safe, and consistent with the validated KB update system.

## Alignment Check

| Area | Expected | Actual | Status | Notes |
|------|----------|--------|--------|------|
| Task is the central unit | GitNexus should make task the root object for plans, changes, KB updates, reports, and commits. | `21_gitnexus_system_design.md` explicitly states that task is the central unit and everything starts from a task. | Pass | Matches the system design goal. |
| Task fields match metadata baseline | Task structure should align with `13_gitnexus_metadata_baseline.md`. | Core fields are present: `task_id`, title, description, domain, `affected_paths`, `affected_kb_files`, priority, `risk_level`, status, `created_at`. | Partial | Missing or under-specified baseline fields include `branch_name`, `commit_hash`, `validation_report`, `kb_update_required`, `kb_update_status`, `reviewer_status`, and `updated_at`. |
| Task lifecycle includes KB update before commit | KB update must happen before commit when required. | Lifecycle step 7 triggers KB update, step 8 generates report, and step 9 links commit. | Pass | Correct sequence. |
| Task to KB mapping uses `kb-manifest.yaml` | `affected_paths` should map through manifest to domains and KB files. | Design uses `affected_paths -> kb-manifest.yaml -> affected domains -> affected KB files`. | Pass | Consistent with KB update policy. |
| Commit metadata includes `task_id` and KB update status | Commit metadata should include task linkage and KB status. | Design states commit metadata must include `task_id` and KB update status. | Pass | Commit message format is not yet defined. |
| Manual first implementation is clearly scoped | First implementation should be manual and safe. | Design scopes first implementation to manual task creation, manual `changed_paths`, manual KB trigger, manual report, and manual commit metadata check. | Pass | No MCP, hooks, CI, or automation required yet. |
| Oracle MCP, CI, and automated git diff are future work | These should not be part of the first implementation. | Design lists Oracle MCP, git hooks, CI/CD, and automated changed_paths extraction under Future Integration. | Pass | Correctly deferred. |
| Failure conditions are explicit | Missing paths, missing KB update, drift, wrong mapping, and unsafe review behavior should be listed. | Failure conditions are listed and include task without affected paths, commit without KB update, KB drift, wrong domain mapping, missing report, missing `task_id`, incomplete KB update, and unsafe review auto-update. | Pass | Good coverage for design stage. |

## Missing Design Elements

- Task status model:
  - Status: Partial
  - The design includes `status` as a task field, but does not define allowed task status values or transitions.
- Branch naming rule:
  - Status: Needs Review
  - `13_gitnexus_metadata_baseline.md` includes `branch_name`, but `21_gitnexus_system_design.md` does not define a branch naming convention.
- Commit message format:
  - Status: Needs Review
  - The design requires commit metadata, but does not define a commit message format or metadata block format.
- KB update report link rule:
  - Status: Partial
  - The design says KB reports should be linked to task metadata, but does not define the exact required field, path format, or validation rule.
- Validation report link rule:
  - Status: Needs Review
  - The metadata baseline includes `validation_report`, but the GitNexus design does not define when validation reports are required or how they are linked.
- Reviewer/approval status:
  - Status: Needs Review
  - The metadata baseline includes `reviewer_status`, but the design does not define approval states, approver ownership, or review blocking behavior.
- Risk level usage:
  - Status: Partial
  - The design includes `risk_level`, but does not define how risk level changes workflow requirements, review depth, or KB validation strictness.

## Safety Check

- GitNexus does not directly modify application code:
  - Status: Pass
  - The design is coordination-only and does not introduce application-code modification behavior.
- KB update enforcement is placed correctly:
  - Status: Pass
  - Enforcement is tied to `affected_paths`, manifest mapping, KB update status, and commit readiness.
- Manual implementation stage is safe:
  - Status: Pass
  - The first implementation is manual, documentation-oriented, and explicitly excludes MCP, hooks, CI, and automated diff extraction.
- Review-required domains remain protected:
  - Status: Pass
  - The design includes failure behavior for review-required domains being auto-updated without concrete diff evidence.
- Residual risk:
  - Status: Partial
  - Without a branch naming rule, commit format, validation report link, and reviewer status model, the workflow can proceed to policy design but is not complete enough for automation.

## Final Verdict

PARTIAL PASS: Can proceed with listed fixes.

The design is safe and aligned with the validated KB update system. It is ready for a GitNexus workflow policy document, but that policy should close the missing operational details before implementation.

Recommended next file:

- `ai/domain-kb/23_gitnexus_workflow_policy.md`
