# 07 GitNexus Manual Baseline Freeze

## Purpose

Declare the GitNexus manual workflow baseline complete and ready for real project usage.

This baseline freeze confirms that GitNexus can now be used manually to coordinate real project tasks from request intake through task metadata, planning, implementation, validation, commit readiness, and Domain KB review.

This is not an automation release. It does not create schemas, CI/CD gates, Orkestra coordination, a GitNexus MCP server, Git operations, or code changes.

## 1. Confirmed Completed Components

| Component | Source Document | Status |
|-----------|-----------------|--------|
| Task format | `ai/gitnexus/docs/02_gitnexus_manual_task_format.md` | Complete |
| Plan format | `ai/gitnexus/docs/03_gitnexus_manual_plan_format.md` | Complete |
| Validation format | `ai/gitnexus/docs/04_gitnexus_manual_validation_format.md` | Complete |
| Commit gate format | `ai/gitnexus/docs/05_gitnexus_manual_commit_gate_format.md` | Complete |
| Workflow validation | `ai/gitnexus/docs/06_gitnexus_manual_workflow_validation.md` | Complete |

Baseline support documents:

- `ai/gitnexus/README.md`
- `ai/gitnexus/docs/00_gitnexus_module_plan.md`
- `ai/gitnexus/docs/01_gitnexus_contract_mapping.md`
- `ai/rules.md`
- `ai/architecture.md`

## 2. Declared Workflow

GitNexus manual workflow is declared as:

```text
User Request
-> Task
-> Plan
-> Implementation
-> Validation
-> Commit Gate
-> Domain KB review
```

Workflow meaning:

- User Request: source intent is captured.
- Task: task metadata, scope, affected domains, expected files, validation need, KB need, and blockers are recorded.
- Plan: implementation scope, risk, expected files, Oracle evidence need, validation steps, and KB review impact are approved before work.
- Implementation: code or documentation work happens only after scope is clear.
- Validation: required checks are performed and recorded.
- Commit Gate: commit readiness is decided manually from task, plan, validation, KB, reviewer, blocker, and rule status.
- Domain KB review: KB impact is evaluated and completed or explicitly skipped with reason when policy allows.

## 3. Explicitly Out Of Scope

The following remain out of scope for this baseline:

- automation
- schemas
- CI/CD integration
- Orkestra MCP
- GitNexus MCP server
- automatic task generation
- automatic plan generation
- automatic validation generation
- automatic commit gate execution
- automatic staging
- automatic commits
- Git reset, checkout, clean, or discard behavior
- Docker dependency
- application code modification by GitNexus itself
- Domain KB modification by GitNexus itself

## 4. Rules For Using GitNexus On Real Tasks

Real project usage must follow these rules:

- Create or identify a GitNexus task before non-trivial implementation.
- Use a stable `task_id` for the task lifecycle.
- Record task type, source request, affected domains, expected files, validation requirement, KB update requirement, reviewer status, and blockers.
- Create a plan before implementation when behavior, routes, schema, services, views, security, admin flows, or KB policy may be affected.
- Keep implementation within the approved plan scope.
- Use Oracle evidence when ownership, route binding, controller ownership, model ownership, permission impact, filter impact, schema impact, or affected files are unclear.
- Validate route, security, schema, order, cart, payment, checkout, builder, cross-domain, high-risk, and critical-risk work before commit readiness.
- Evaluate KB impact through Domain KB policy and manifest mapping.
- If KB update is required, complete it or record an approved skip reason where policy allows.
- Run the manual commit gate before any commit decision.
- Do not mark `commit_allowed` true while task, plan, validation, KB decision, reviewer status, or blockers are incomplete.
- Respect `ai/rules.md` and `ai/architecture.md` at every stage.
- Preserve UTF-8 without BOM and avoid encoding corruption.
- Do not use GitNexus as a reason for scope creep, refactor drift, hidden file changes, or bypassing review.

## 5. First Real Usage Directive

First real usage directive:

```text
Use GitNexus manually on the next meaningful project change that affects code, behavior, routes, schema, security, admin workflows, user workflows, Domain KB policy, or commit readiness.
```

Minimum required real usage sequence:

1. Create or draft a real task record under the future task workflow.
2. Create or draft the linked plan before implementation.
3. Implement only the approved scope.
4. Produce validation evidence when required.
5. Evaluate Domain KB impact.
6. Run the manual commit gate.
7. Commit only if the gate allows it and the user explicitly chooses to commit separately.

No automatic commit, Git command, schema, or automation is implied by this directive.

## 6. Risks If Workflow Is Bypassed

| Risk | Impact |
|------|--------|
| Missing task metadata | Work cannot be traced to source request, scope, domains, validation, or KB decision. |
| Missing plan | Implementation may drift into refactor, redesign, route changes, schema changes, or unrelated files. |
| Missing validation | Broken behavior, permission leaks, route regressions, schema errors, or UI regressions may reach commit readiness. |
| Missing KB review | Domain KB can drift from repository state. |
| Missing commit gate | Commit readiness may be declared without task, plan, validation, KB, reviewer, or blocker evidence. |
| Missing Oracle evidence when needed | Inactive files, legacy controllers, wrong routes, or unclear ownership may be modified. |
| Bypassing `ai/rules.md` | Security, permission boundaries, data integrity, encoding, admin stability, or architecture may be harmed. |
| Premature automation | Unvalidated assumptions may become enforced incorrectly. |
| Hidden file changes | Reviewers cannot trust the task scope or commit readiness decision. |

## 7. Pass / Warning / Blocker Summary

| Area | Status | Notes |
|------|--------|------|
| Manual task format | Pass | Ready for real manual usage. |
| Manual plan format | Pass | Ready for real manual usage. |
| Manual validation format | Pass | Ready for real manual usage. |
| Manual commit gate format | Pass | Ready for real manual usage as advisory/manual gate. |
| Workflow validation | Pass | Documentation-only dry-run confirmed representability. |
| Real project usage | Pass | Manual usage may begin with explicit records and scoped work. |
| Automation | Warning | Still intentionally out of scope. |
| Schemas | Warning | Still intentionally out of scope until manual usage proves stable. |
| CI/CD integration | Warning | Not part of this baseline. |
| Orkestra MCP | Warning | Not part of this baseline. |
| GitNexus MCP server | Warning | Not part of this baseline. |
| Format alignment | Warning | Future work should normalize KB status naming and dry-run id conventions before schemas. |
| Blockers | None | No blocker to using the manual baseline on real project tasks. |

## Final Decision

GitNexus Manual Baseline Ready For Real Usage: YES
