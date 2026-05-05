# 05 GitNexus Manual Commit Gate Format

## Purpose

Define the first manual GitNexus commit gate format and decision rules.

This document defines how future commit gate files should be written before schemas, automation, actual commit gate executions, Git integration, CI gates, or commit automation exist.

The commit gate is an advisory readiness decision. It does not stage files, create commits, run Git commands, run Docker, modify code, modify `app/`, modify Domain KB, or bypass validation.

## 1. Future Commit Gate Folder And Naming Convention

Future folder:

```text
ai/gitnexus/commit-gate/
```

Filename pattern:

```text
GNX-0001_commit_gate.md
```

Linked task id rules:

- Every commit gate must link to exactly one primary `task_id`.
- The `task_id` must match an existing future task file.
- The filename must begin with the linked `task_id`.
- A commit gate must not be created for an undefined task.

Linked evidence rules:

- A commit gate must link to the approved plan.
- A commit gate must link to the active validation report.
- If KB update is required, the gate must link to the KB update file or record an approved skip reason.
- If any linked file is missing, the gate must be `blocked` or `reject_commit`.

## 2. Required Commit Gate Metadata Fields

| Field | Required | Allowed / Expected Value | Notes |
|------|----------|--------------------------|------|
| `task_id` | Yes | `GNX-0001` pattern | Must link to one task. |
| `commit_title` | Yes | short human-readable title | Should describe the proposed commit. |
| `created_at` | Yes | ISO 8601 timestamp | Example: `2026-05-01T12:00:00+03:00`. |
| `commit_status` | Yes | allowed commit status list below | Manual commit gate lifecycle state. |
| `linked_task_file` | Yes | relative path or `pending` | Must point to future task file before approval. |
| `linked_plan_file` | Yes | relative path or `pending` | Must point to approved plan. |
| `linked_validation_file` | Yes | relative path or `pending` | Must point to active validation report. |
| `kb_update_required` | Yes | `true` / `false` | Must be evaluated from affected files and KB policy. |
| `kb_update_completed` | Yes | `true` / `false` | Must be true when KB update is required. |
| `kb_update_file` | Yes when required | relative path, `not_required`, `skipped`, or `pending` | Required when KB update is completed; skipped requires reason. |
| `reviewer_status` | Yes | `not_required`, `pending`, `approved`, `changes_requested` | Must allow commit before final approval. |
| `blockers` | Yes | list or `none` | Must be empty/none before commit is allowed. |
| `commit_allowed` | Yes | `true` / `false` | True only when all decision rules pass. |
| `final_decision` | Yes | allowed final decision list below | Final manual gate result. |
| `notes` | Yes | text or `none` | Non-blocking notes, skip reasons, or review context. |

## 3. Allowed `commit_status` Values

| Status | Meaning |
|--------|---------|
| `draft` | Commit gate exists but is incomplete. |
| `under_review` | Gate is being reviewed against task, plan, validation, KB, reviewer, and rule requirements. |
| `ready` | Gate has enough evidence for final decision. |
| `blocked` | Gate cannot complete because required evidence or approval is missing. |
| `rejected` | Gate failed and commit must not proceed. |
| `approved` | Gate passed and manual commit may proceed if the user chooses to commit separately. |

## 4. Required Markdown Sections For Future Commit Gate Files

Future commit gate files must include these sections:

1. Header
2. Linked Task
3. Linked Plan
4. Linked Validation
5. KB Update Check
6. Reviewer Decision
7. Blockers
8. Final Decision
9. Notes

## 5. Commit Gate Decision Rules

Commit can be allowed only if:

- task exists and is valid
- plan exists and is approved
- validation exists and is `passed` or `passed_with_notes`
- KB update requirement is evaluated
- if KB update is required, it is completed or explicitly skipped with reason
- `reviewer_status` is `approved` or `not_required`
- `blockers` is empty or `none`
- no rule violation exists under `ai/rules.md`
- scope matches the linked task and approved plan
- no hidden file changes are included
- `commit_allowed` is explicitly `true`

Commit must be rejected or blocked if:

- linked task is missing, invalid, or mismatched
- linked plan is missing, not approved, or mismatched
- validation is missing, failed, blocked, superseded, or not the active report
- KB update requirement was not evaluated
- KB update is required but not completed and no approved skip reason exists
- `reviewer_status` is `pending` or `changes_requested`
- blockers are present
- any `ai/rules.md` safety rule is violated
- affected files exceed approved scope
- hidden, unrelated, or surprise changes are detected

## 6. KB Update Enforcement Rules

KB update enforcement must follow the Domain KB update policy and manifest mapping.

If affected files match `ai/domain-kb/kb-manifest.yaml` mapping:

```text
KB update MUST be evaluated.
```

Required mapping chain:

```text
Affected file path -> kb-manifest.yaml -> affected domain -> affected KB files -> update or skip decision
```

Rules:

- If affected files match manifest `exact`, `globs`, or `broad_review` entries, `kb_update_required` must be evaluated.
- If KB update is required, `kb_update_completed` must be `true`.
- If KB update is completed, `kb_update_file` must point to the relevant KB update file or report.
- If KB update is skipped, the skip reason must be documented in `notes`.
- A skipped KB update is allowed only when the skip reason is explicit, source-backed, and policy-compatible.
- If uncertainty remains, the gate must use `blocked`, not skip.
- If the manifest itself appears incomplete for the affected files, the gate must be blocked for KB review.

## 7. Final Decision Values

| Final Decision | Meaning | Commit Allowed |
|----------------|---------|----------------|
| `allow_commit` | All gates passed without blocking notes. | `true` |
| `allow_with_notes` | All gates passed, but non-blocking notes must be retained. | `true` |
| `reject_commit` | One or more required gates failed. | `false` |
| `blocked` | Required evidence, validation, KB decision, reviewer decision, or scope clarity is missing. | `false` |

Decision rules:

- Use `allow_commit` only when every required gate passes cleanly.
- Use `allow_with_notes` only for non-blocking validation notes or documented low-risk exceptions.
- Use `reject_commit` when a rule violation, failed validation, rejected review, or scope violation exists.
- Use `blocked` when the decision cannot be made because required evidence is missing or uncertain.

## 8. Safety Rules

The manual commit gate must enforce these safety rules:

- no auto commit
- no git command execution
- no automatic staging
- no reset, checkout, clean, discard, or file rollback behavior
- no bypass of validation
- no bypass of KB policy
- no hidden file changes
- no scope creep
- no code execution requirement
- no Docker requirement
- no application code modification
- no `app/` modification by GitNexus itself
- no Domain KB modification unless a separate explicit KB task requires it
- no schema creation
- no automation creation
- no encoding corruption
- preserve UTF-8 without BOM

## 9. Minimal Blank Commit Gate Template

Use this template inside future commit gate files. Do not create actual commit gate files until explicitly requested.

```markdown
# GNX-0000 Commit Gate

## Header

| Field | Value |
|------|-------|
| task_id | GNX-0000 |
| commit_title |  |
| created_at |  |
| commit_status | draft |
| linked_task_file | pending |
| linked_plan_file | pending |
| linked_validation_file | pending |
| kb_update_required | false |
| kb_update_completed | false |
| kb_update_file | not_required |
| reviewer_status | pending |
| blockers | none |
| commit_allowed | false |
| final_decision | blocked |
| notes | none |

## Linked Task


## Linked Plan


## Linked Validation


## KB Update Check


## Reviewer Decision


## Blockers


## Final Decision


## Notes

```

## 10. Recommended Next File

Recommended next file:

```text
ai/gitnexus/docs/06_gitnexus_manual_workflow_validation.md
```

Purpose:

- Validate the manual task, plan, validation, and commit gate formats together.
- Keep it documentation-only.
- Do not create schemas yet.
- Do not create automation yet.
- Do not create actual task, plan, validation, or commit gate executions yet.

## 11. Pass / Warning / Blocker Summary

| Area | Status | Notes |
|------|--------|------|
| Commit gate purpose | Pass | Manual advisory commit readiness is defined without Git execution. |
| Future folder and naming | Pass | Future folder and filename pattern are defined. |
| Required metadata | Pass | Manual commit gate metadata fields are defined. |
| Commit status values | Pass | Draft, review, ready, blocked, rejected, and approved states are defined. |
| Required sections | Pass | Future commit gate Markdown sections are defined. |
| Decision rules | Pass | Commit allow, reject, and blocked conditions are explicit. |
| KB enforcement | Pass | Manifest-based KB evaluation, completion, and skip reason rules are defined. |
| Final decisions | Pass | `allow_commit`, `allow_with_notes`, `reject_commit`, and `blocked` are defined. |
| Safety rules | Pass | No auto commit, no Git execution, no validation bypass, no KB bypass, no hidden changes, and no scope creep are enforced. |
| Schemas | Warning | Not created yet by design. |
| Automation | Warning | Not created yet by design. |
| Actual commit gate executions | Warning | Not created yet by design. |
| Blockers | None | No blocker for the next documentation-only workflow validation file. |

## Final Decision

GitNexus Manual Commit Gate Format Complete: YES
