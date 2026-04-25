# 25 GitNexus Contract Readiness

## Purpose

Confirm that the Domain KB side of the GitNexus contract is complete before creating the separate GitNexus module.

## Contract Files

| File | Role | Status | Notes |
|------|------|--------|------|
| `13_gitnexus_metadata_baseline.md` | Metadata baseline | Complete | Defines minimum task, plan, commit, KB update, validation, and review metadata expectations. |
| `21_gitnexus_system_design.md` | System design | Complete | Defines task-centered linkage between plans, repository changes, KB updates, commits, and validation. |
| `22_gitnexus_design_validation.md` | Design validation | Complete | Validates the system design and identifies workflow policy gaps that were later addressed. |
| `23_gitnexus_workflow_policy.md` | Workflow policy | Complete | Defines task status, task type, branch naming, commit message, report linking, reviewer status, risk gates, and multi-task linkage rules. |
| `24_gitnexus_workflow_policy_validation.md` | Workflow policy validation | Complete | Confirms the workflow policy is ready for GitNexus implementation planning. |

## What Is Complete

- Task metadata baseline
- Task lifecycle
- Task type model
- Branch naming policy
- Commit message policy
- KB update report link rule
- Validation report link rule
- Reviewer approval policy
- Risk-level workflow impact
- Multi-task / multi-commit linkage
- Workflow gates
- Failure conditions
- Minimal manual workflow boundary
- GitNexus implementation readiness validation

## What Is Not Implemented Yet

- GitNexus task store
- Machine-readable task schema
- Branch/commit checker
- Changed-path extractor
- GitNexus MCP
- CI gate
- Oracle MCP integration

## Boundary Decision

- Domain KB contains the GitNexus contract only.
- Actual GitNexus implementation must live outside `ai/domain-kb`.
- Future implementation location should be `ai/gitnexus/`.
- Domain KB should remain the source of contract expectations, policy rules, and validation evidence.
- GitNexus implementation should consume this contract rather than embedding unrelated application logic into Domain KB.

## Readiness Decision

PASS: Domain KB GitNexus contract is ready. Proceed to `ai/gitnexus` implementation planning.
