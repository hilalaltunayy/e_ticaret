# 00 GitNexus Module Plan

## Purpose

Define the first concrete GitNexus module boundary and manual-first planning direction.

This document is not an implementation. It does not define schemas, create automation, run Git, modify application code, modify Domain KB, or depend on Orkestra.

## Source References

- `ai/63_gitnexus_module_start_plan.md`
- `ai/domain-kb/13_gitnexus_metadata_baseline.md`
- `ai/domain-kb/21_gitnexus_system_design.md`
- `ai/domain-kb/23_gitnexus_workflow_policy.md`
- `ai/domain-kb/25_gitnexus_contract_readiness.md`
- `ai/oracle/60_oracle_mcp_capability_report.md`
- `ai/oracle/62_oracle_mcp_post_expansion_docker_validation.md`
- `ai/rules.md`
- `ai/architecture.md`

## 1. Module Boundary

GitNexus is responsible for task-centered coordination.

It connects:

- source request
- task metadata
- affected domains
- affected files
- Oracle MCP evidence
- plan metadata
- validation evidence
- Domain KB update decision
- commit readiness

GitNexus is not responsible for:

- editing application code
- executing application code
- running Docker
- committing files
- staging files
- resetting or cleaning Git state
- replacing Domain KB
- replacing Oracle MCP
- replacing Task Skill, Plan Skill, or Tester Skill
- coordinating Orkestra MCP

## 2. Folder Responsibility

Proposed future responsibilities:

| Folder | Responsibility | Current Phase |
|--------|----------------|---------------|
| `ai/gitnexus/` | GitNexus module root and entry documentation | Created for documentation only |
| `ai/gitnexus/docs/` | Planning, contract mapping, and validation documents | Created for documentation only |
| `ai/gitnexus/schemas/` | Future machine-readable schemas | Not created yet |
| `ai/gitnexus/tasks/` | Future task records | Not created yet |
| `ai/gitnexus/plans/` | Future task-linked plans | Not created yet |
| `ai/gitnexus/validations/` | Future validation reports | Not created yet |
| `ai/gitnexus/commit-gate/` | Future advisory commit gate docs and later implementation | Not created yet |

Current creation scope:

- `ai/gitnexus/README.md`
- `ai/gitnexus/docs/00_gitnexus_module_plan.md`

No other folder or file should be created until a separate explicit task approves it.

## 3. Manual-First Workflow

The first GitNexus workflow must be manual-first:

```text
User request
-> Task metadata draft
-> Oracle evidence lookup if needed
-> Plan draft
-> Codex implementation
-> Validation report
-> Domain KB update decision
-> Commit gate decision
```

Manual-first rules:

- Metadata is written and reviewed by humans or AI assistants, not enforced automatically.
- Oracle evidence is referenced only when useful.
- Domain KB update decisions are based on `kb-manifest.yaml`.
- Commit readiness is a documented decision, not an automatic Git operation.
- Every skip decision needs a reason.

## 4. Minimum Metadata Model

Minimum task-level metadata:

| Field | Required | Purpose |
|------|----------|---------|
| `task_id` | Yes | Stable task identifier such as `GNX-0001`. |
| `task_title` | Yes | Human-readable task name. |
| `task_type` | Yes | Work type such as `feature`, `fix`, `docs`, `security`, `route`, `schema`, or `kb_update`. |
| `source_request` | Yes | Original request summary. |
| `affected_domains` | Yes | Domain names aligned with Domain KB. |
| `affected_files` | Yes | Expected or actual changed/reviewed paths. |
| `oracle_evidence` | No | Oracle MCP tool evidence references. |
| `plan_file` | Yes after planning | Path to linked plan. |
| `validation_file` | Required when validation is required | Path to linked validation report. |
| `kb_update_required` | Yes | Whether Domain KB review/update is required. |
| `commit_allowed` | Yes | Whether commit gate is satisfied. |
| `status` | Yes | Current task lifecycle status. |

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

## 5. Future Schema Plan

Schemas should be introduced only after manual file formats are validated.

Future schema candidates:

- `ai/gitnexus/schemas/task.schema.json`
- `ai/gitnexus/schemas/plan.schema.json`
- `ai/gitnexus/schemas/validation.schema.json`
- `ai/gitnexus/schemas/commit_gate.schema.json`

Schema rules:

- Must follow the metadata baseline in Domain KB.
- Must preserve task-centered linkage.
- Must include KB update status.
- Must include validation status when required.
- Must include commit readiness.
- Must support Oracle evidence references.
- Must not require automation in the first version.

## 6. Future Validation Plan

Validation should be introduced in stages:

1. Validate documentation-only GitNexus module start.
2. Validate contract mapping against Domain KB.
3. Validate manual task metadata format.
4. Validate manual plan format.
5. Validate manual validation report format.
6. Validate commit gate decision format.
7. Validate schemas after manual formats stabilize.

Validation must check:

- required metadata exists
- affected files are listed
- affected domains are mapped
- Oracle evidence is cited when used
- KB update requirement is decided
- validation requirement is decided
- commit gate decision is explicit

## 7. Future Commit Gate Plan

Commit Gate should remain advisory/manual at first.

It should check:

- task id exists
- task type exists
- affected files exist
- affected domains exist
- plan file exists when required
- validation file exists when required
- KB update report or skip reason exists when required
- reviewer status is acceptable
- `commit_allowed` is true

Commit Gate must not:

- stage files
- create commits
- reset files
- checkout files
- clean files
- modify application code
- modify Domain KB automatically

## 8. Risks

| Risk | Mitigation |
|------|------------|
| Duplicate task systems | Treat GitNexus as the future coordination layer while keeping legacy `ai/tasks/` as backlog/reference until migration is planned. |
| Confusing contract with implementation | Domain KB contains GitNexus contract; this module starts implementation planning only. |
| Premature schemas | Validate manual formats before creating schemas. |
| Premature automation | Keep first phase documentation-only and manual. |
| App modification risk | GitNexus must never modify `app/` directly. |
| Commit operation risk | GitNexus must not stage, commit, reset, checkout, clean, or discard files in the first phase. |
| Oracle overreach | Oracle evidence should support task planning, not become uncontrolled broad discovery. |
| Domain KB drift | KB update expectations must be checked through `kb-manifest.yaml`. |
| Encoding issues | Preserve UTF-8 without BOM and avoid mojibake. |

## 9. First Safe Next Task After This

Recommended next task:

```text
Create ai/gitnexus/docs/01_gitnexus_contract_mapping.md only.
```

That document should map:

- Domain KB metadata baseline to GitNexus task fields
- GitNexus workflow policy to manual lifecycle steps
- Oracle MCP capabilities to evidence reference fields
- KB update policy to commit readiness fields

Do not create schemas, automation, task examples, plan examples, validation examples, or commit gate implementation yet.

## Final Decision

GitNexus documentation module start is ready for validation.
