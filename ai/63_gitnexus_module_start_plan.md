# 63 GitNexus Module Start Plan

## Purpose

Define the first GitNexus module start plan based on the current AI workspace baseline.

This document is planning-only. It does not create `ai/gitnexus/`, does not create schemas, does not modify code, does not modify `app/`, does not modify Domain KB, does not run Docker, and does not create Git commits.

## Source-of-Truth References

- `ai/61_ai_workspace_full_discovery_report.md`
- `ai/domain-kb/13_gitnexus_metadata_baseline.md`
- `ai/domain-kb/21_gitnexus_system_design.md`
- `ai/domain-kb/23_gitnexus_workflow_policy.md`
- `ai/domain-kb/25_gitnexus_contract_readiness.md`
- `ai/oracle/60_oracle_mcp_capability_report.md`
- `ai/oracle/62_oracle_mcp_post_expansion_docker_validation.md`
- `ai/rules.md`
- `ai/architecture.md`

## 1. GitNexus Purpose

GitNexus is the task-centered coordination layer for this project.

Its purpose is to connect:

- user requests
- task metadata
- plans
- affected repository paths
- Oracle MCP evidence
- validation reports
- Domain KB update expectations
- commit readiness
- final commit linkage

GitNexus must answer, for every meaningful change:

- Why does this work exist?
- Which task owns it?
- Which domains are affected?
- Which files are expected or actually changed?
- Which evidence supports the plan?
- Is validation required?
- Is a Domain KB update required?
- Is commit allowed?

## 2. What GitNexus Must Manage

| Managed Area | Required Behavior | Source Reference |
|--------------|-------------------|------------------|
| Task metadata | Store stable task fields such as `task_id`, `task_title`, `task_type`, domains, risk, and status. | `13_gitnexus_metadata_baseline.md`, `21_gitnexus_system_design.md` |
| Plan metadata | Link plans to tasks and include steps, expected changes, KB impact, and risk assessment. | `21_gitnexus_system_design.md` |
| Affected files | Track expected and actual changed paths. | `13_gitnexus_metadata_baseline.md`, `23_gitnexus_workflow_policy.md` |
| Validation evidence | Link validation reports required by risk, security, route, schema, or RBAC impact. | `23_gitnexus_workflow_policy.md` |
| Commit readiness | Decide whether required task, plan, KB, validation, and approval gates are complete. | `23_gitnexus_workflow_policy.md` |
| Domain KB update expectation | Use `kb-manifest.yaml` mapping results to determine whether KB review/update is required. | `21_gitnexus_system_design.md`, `25_gitnexus_contract_readiness.md` |
| Oracle evidence references | Store evidence paths/results from Oracle lookup tools when used for planning or validation. | `60_oracle_mcp_capability_report.md`, `62_oracle_mcp_post_expansion_docker_validation.md` |

## 3. What GitNexus Must NOT Do Yet

GitNexus must not do the following in the first module phase:

- No code execution.
- No application code modification.
- No automatic commits.
- No automatic staging.
- No Git reset/checkout/clean behavior.
- No CI gate yet.
- No production enforcement.
- No Docker dependency.
- No Orkestra dependency.
- No automatic Domain KB editing.
- No real MCP server dependency.
- No broad repository discovery unless explicitly needed for a scoped task.

First phase behavior should remain manual, documentation-first, and evidence-driven.

## 4. Proposed Future Folder Structure

The future implementation location should be:

```text
ai/gitnexus/
  README.md
  docs/
    00_gitnexus_module_plan.md
    01_contract_mapping.md
    02_manual_workflow_validation.md
  schemas/
    task.schema.json
    plan.schema.json
    validation.schema.json
    commit_gate.schema.json
  tasks/
    GNX-0001_example_task.md
  plans/
    GNX-0001_example_plan.md
  validations/
    GNX-0001_example_validation.md
  commit-gate/
    README.md
```

Creation rule:

- Do not create this structure until a separate explicit implementation task approves it.
- Start with docs before schemas.
- Start with manual examples before automation.

## 5. Minimum GitNexus Data Model Draft

| Field | Type | Required | Purpose |
|------|------|----------|---------|
| `task_id` | string | Yes | Stable identifier, e.g. `GNX-0001`. |
| `task_title` | string | Yes | Human-readable task title. |
| `task_type` | string | Yes | `feature`, `fix`, `docs`, `refactor`, `chore`, `kb_update`, `security`, `route`, `schema`, or `ui`. |
| `source_request` | string | Yes | Original user/system request summary. |
| `affected_domains` | array | Yes | Domains affected by the task. Must align with Domain KB domain names. |
| `affected_files` | array | Yes | Expected or actual changed/reviewed repository paths. |
| `oracle_evidence` | array | No | Source references from Oracle MCP lookup tools, including tool name, query, and evidence path. |
| `plan_file` | string/null | Yes after planning | Path to plan file. |
| `validation_file` | string/null | Required when validation is required | Path to validation report. |
| `kb_update_required` | boolean | Yes | Whether Domain KB review/update is required. |
| `commit_allowed` | boolean | Yes | Whether all gates allow commit. |
| `status` | string | Yes | Uses GitNexus workflow status model. |

Recommended status values:

- `draft`
- `ready_for_plan`
- `planned`
- `in_progress`
- `kb_update_required`
- `validation_required`
- `ready_for_commit`
- `committed`
- `closed`
- `blocked`

## 6. GitNexus Workflow Draft

```text
User request
-> Task creation
-> Oracle evidence lookup if needed
-> Plan creation
-> Implementation by Codex
-> Validation report
-> KB update decision
-> Commit gate decision
```

### Step Detail

| Step | Output | Gate |
|------|--------|------|
| User request | `source_request` captured | Must be clear enough to classify task type and risk. |
| Task creation | Task metadata file | Must include task id, type, domain, expected paths, and risk level. |
| Oracle evidence lookup if needed | Evidence references | Required for unclear route, controller, model, permission, filter, or schema ownership. |
| Plan creation | Plan file | Must include expected changes, KB impact, validation expectations, and safety notes. |
| Implementation by Codex | Changed files | Must stay within task scope and project rules. |
| Validation report | Validation file | Required for high-risk, security, route, schema, or RBAC changes. |
| KB update decision | KB update report or skip reason | Required when affected paths match Domain KB manifest impact. |
| Commit gate decision | `commit_allowed: true/false` | Commit allowed only when required KB, validation, and approval gates are satisfied. |

## 7. Relationship With Existing Systems

### Domain KB

- Domain KB remains the source of truth for domain mapping, KB update policy, route/security/schema baselines, and GitNexus contract.
- GitNexus must consume Domain KB policy rather than duplicating it.
- `kb-manifest.yaml` is the mapping source for path-to-domain and path-to-KB impact.

### Oracle MCP

- Oracle MCP provides read-only evidence.
- Current implemented lookup tools can support GitNexus planning:
  - `repo_file_lookup`
  - `route_lookup`
  - `model_lookup`
  - `controller_lookup`
  - `permission_lookup`
  - `filter_lookup`
- Oracle MCP should not create commits or modify application code.

### Task Skill

- Future Task Skill should create or normalize GitNexus task metadata.
- It should not replace GitNexus.
- It should output task drafts that GitNexus can store.

### Plan Skill

- Future Plan Skill should create task-linked plans.
- Plans must include affected files, risk, expected validation, and KB update impact.
- It should write under future `ai/gitnexus/plans/` or `ai/plans/` only after a plan storage policy exists.

### Tester Skill

- Future Tester Skill should generate validation evidence linked to task metadata.
- It should distinguish manual, local, Docker, security, route, schema, and UI validation.

### Commit Gate

- Commit Gate should be advisory/manual first.
- It should block only by policy decision, not by automatic Git operation in the first phase.
- It should require task id, KB status, validation status, reviewer status, and changed paths.

### Future Orkestra MCP

- Orkestra MCP should not be introduced yet.
- Orkestra can coordinate Domain KB, GitNexus, Oracle MCP, Task Skill, Plan Skill, Tester Skill, and Commit Gate later.
- It depends on GitNexus having a stable task/plan/commit data model first.

## 8. Risks and Safety Rules

| Risk | Mitigation |
|------|------------|
| Duplicate task systems | Treat GitNexus as the authoritative task metadata layer once implemented; keep legacy `ai/tasks/` as task backlog/reference until migrated. |
| Uncontrolled refactors | Enforce `ai/rules.md`: smallest scoped change, no surprise refactors, no broad rewrites. |
| Encoding/mojibake issues | Avoid broad formatting. Use UTF-8 without BOM. Fix documentation encoding only in explicit cleanup tasks. |
| Moving source-of-truth files | Do not move Domain KB, root architecture docs, Oracle capability docs, or task archives without an archive plan. |
| Broad discovery by default | Use Oracle lookup tools for scoped evidence; avoid full repo scans unless task explicitly requires them. |
| Confusing contract with implementation | Domain KB GitNexus docs are complete contract, not implementation. `ai/gitnexus/` still needs a start plan and validation. |
| Premature automation | Start manual. Add schema before scripts. Add validation before enforcement. |
| Commit gate overreach | First commit gate should report readiness, not stage/commit/reset/checkout. |
| Orkestra too early | Delay Orkestra until GitNexus and Oracle interfaces are stable. |

## 9. First Implementation Milestone After This Plan

Recommended next milestone:

```text
Create ai/gitnexus/00_gitnexus_module_plan.md and ai/gitnexus/README.md in a separate explicit task.
```

Minimum allowed first implementation scope:

- Create `ai/gitnexus/`.
- Create `ai/gitnexus/README.md`.
- Create `ai/gitnexus/docs/00_gitnexus_module_plan.md`.
- Do not create schemas yet.
- Do not create automation.
- Do not run Git commands.
- Do not modify `app/`.
- Do not modify Domain KB.

The first module plan should map the Domain KB GitNexus contract into a concrete GitNexus folder/module boundary.

## 10. Pass / Warning / Blocker Summary

| Area | Status | Notes |
|------|--------|------|
| Domain KB GitNexus contract | Pass | Contract readiness is complete in `25_gitnexus_contract_readiness.md`. |
| GitNexus purpose | Pass | Task-centered coordination layer is clearly defined. |
| Minimum metadata model | Pass | Baseline fields are available from `13_gitnexus_metadata_baseline.md`. |
| Workflow policy | Pass | Status model, task type model, branch/commit rules, validation, KB update, approval, and gates exist. |
| Oracle evidence provider | Pass with warning | Oracle runtime has six read-only tools; Docker post-expansion execution validation is still recommended. |
| GitNexus implementation folder | Warning | `ai/gitnexus/` does not exist yet. |
| Machine-readable schemas | Warning | Not created yet by design. |
| Automation / CI gate | Warning | Not implemented and should remain out of scope for the first module step. |
| Blockers | None | No blocker for creating the first GitNexus docs-only module folder in a separate explicit task. |

## Final Decision

GitNexus Module Start Plan Complete: YES
