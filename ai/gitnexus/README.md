# GitNexus

## Purpose

GitNexus is the task-centered coordination module for this project.

Its purpose is to connect user requests, task metadata, plans, affected repository paths, Oracle MCP evidence, validation reports, Domain KB update expectations, commit readiness, and final commit linkage.

GitNexus does not replace Domain KB or Oracle MCP. It consumes their evidence and policies so each meaningful repository change can be traced from request to task, plan, validation, KB decision, and commit readiness.

## Current Phase

Current phase:

```text
documentation-only module start
```

This folder currently defines the GitNexus module boundary and first planning rules only.

No schemas, automation, task store, plan store, validation examples, commit gate implementation, MCP server, CI gate, or Git integration exists yet.

## What GitNexus Manages

GitNexus is expected to manage:

- task metadata
- plan metadata
- affected domains
- affected files
- Oracle MCP evidence references
- validation evidence
- Domain KB update expectation
- commit readiness
- reviewer and approval status
- task lifecycle status

Minimum future metadata includes:

- `task_id`
- `task_title`
- `task_type`
- `source_request`
- `affected_domains`
- `affected_files`
- `oracle_evidence`
- `plan_file`
- `validation_file`
- `kb_update_required`
- `commit_allowed`
- `status`

## What GitNexus Does Not Do Yet

GitNexus must not do these things in the current phase:

- no code execution
- no application code modification
- no automatic commits
- no automatic staging
- no Git reset, checkout, clean, or discard behavior
- no CI gate
- no production enforcement
- no Docker dependency
- no Orkestra dependency
- no automatic Domain KB editing
- no real MCP server dependency
- no schemas yet
- no task, plan, or validation examples yet

## Relationship With Existing Systems

### Domain KB

Domain KB remains the source of truth for domain mapping, KB update policy, route/security/schema baselines, claim IDs, and GitNexus contract expectations.

GitNexus must consume:

- `ai/domain-kb/13_gitnexus_metadata_baseline.md`
- `ai/domain-kb/21_gitnexus_system_design.md`
- `ai/domain-kb/23_gitnexus_workflow_policy.md`
- `ai/domain-kb/25_gitnexus_contract_readiness.md`
- `ai/domain-kb/kb-manifest.yaml`

### Oracle MCP

Oracle MCP is the read-only repository evidence provider.

Current Oracle tools can support GitNexus planning and validation:

- `repo_file_lookup`
- `route_lookup`
- `model_lookup`
- `controller_lookup`
- `permission_lookup`
- `filter_lookup`

GitNexus should store Oracle evidence references, not raw uncontrolled repo scans.

### Task Skill

Future Task Skill should create or normalize GitNexus task metadata.

It should not replace GitNexus. It should produce task drafts that GitNexus can store and validate.

### Plan Skill

Future Plan Skill should create task-linked plans.

Plans must include expected changes, affected files, KB impact, risk assessment, validation expectations, and safety notes.

### Tester Skill

Future Tester Skill should create validation evidence linked to task metadata.

It should distinguish manual validation, local runtime checks, Docker validation, route/security/schema validation, and UI validation.

### Commit Gate

Future Commit Gate should decide whether a commit is allowed based on task metadata, KB update status, validation status, reviewer status, and changed paths.

The first Commit Gate must be advisory/manual only. It must not stage, commit, reset, checkout, clean, or discard files.

## Safety Rules

GitNexus must follow these rules:

- Do not modify `app/`.
- Do not execute application code.
- Do not make automatic commits.
- Do not bypass Domain KB policy.
- Do not invent repository facts.
- Use Oracle MCP evidence when source ownership is unclear.
- Use Domain KB manifest mapping for KB impact decisions.
- Keep manual-first behavior until schemas and validations are proven.
- Do not create broad repository discovery flows unless a task explicitly requires them.
- Do not move source-of-truth files.
- Do not introduce encoding corruption or mojibake.
- Preserve UTF-8 without BOM.

## Next Milestones

Recommended next milestones:

1. Validate this documentation-only module start.
2. Create a GitNexus contract mapping document.
3. Define manual task file format.
4. Define manual plan file format.
5. Define manual validation file format.
6. Create schemas only after the manual formats are validated.
7. Design advisory commit gate behavior.
8. Review future Oracle MCP integration boundary.

## Current Decision

GitNexus is ready for documentation-only planning.

It is not ready for automation, schemas, CI, commits, or Orkestra coordination yet.
