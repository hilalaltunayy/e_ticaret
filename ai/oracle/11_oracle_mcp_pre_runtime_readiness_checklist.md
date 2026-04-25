# 11 Oracle MCP Pre-Runtime Readiness Checklist

## Purpose

Before creating any runtime folder, Dockerfile, MCP server code, or Oracle connection files, define a strict readiness checklist.

This document is still planning-only. It does not create runtime files, Docker files, MCP code, automation, scripts, or secrets.

## Current Safe State

- Documentation only.
- No runtime folder.
- No Docker implementation.
- No MCP implementation.
- `app/` untouched.
- No secrets.
- No Oracle connection files.
- No container created.
- No image built.

## Required Local Checks Before Implementation

Confirm before implementation starts:

- Docker Desktop installed.
- Docker Desktop running.
- Basic `docker version` check works.
- Project path confirmed.
- Git status clean or intentionally dirty.
- Dirty git state is understood before creating runtime files.
- `.gitignore` reviewed.
- Local environment is ready for a read-only repository mount.
- No real credentials are present in repository files.

## Required Project Checks

Confirm before implementation starts:

- `ai/oracle/07_oracle_mcp_tool_schema_validation.md` exists.
- `ai/oracle/08_oracle_mcp_implementation_plan.md` exists.
- `ai/oracle/09_oracle_mcp_implementation_plan_validation.md` exists.
- `ai/oracle/10_oracle_mcp_beginner_setup_guide.md` exists.
- `09_oracle_mcp_implementation_plan_validation.md` says `PASS`.
- `10_oracle_mcp_beginner_setup_guide.md` exists and has been reviewed.
- `app/` must remain read-only unless explicitly approved in a later implementation task.
- `ai/oracle/runtime/` does not exist yet.

## Required Secret Checks

Confirm before implementation starts:

- No Oracle password in repo.
- No wallet/private key in repo.
- No real credentials in prompts.
- No real credentials in Domain KB.
- No real credentials in Oracle docs.
- No real credentials in GitNexus metadata.
- Future `.env` must be local-only and gitignored.
- Future `.env.example` may contain placeholders only.
- Secrets must not be logged.
- Secrets must not appear in tool output.

## Runtime Creation Gate

`ai/oracle/runtime/` may be created only when all of the following are true:

- Docker Desktop is installed and running.
- Project path is confirmed.
- Git status is clean or intentionally dirty and understood.
- `.gitignore` has been reviewed for future `.env` protection.
- Secret handling rules are understood.
- The next task explicitly authorizes runtime folder creation.
- The task confirms that no `app/` writes are allowed.
- The task confirms that runtime creation is implementation, not planning.

If any condition is false, the runtime folder must not be created.

## Docker Creation Gate

Dockerfile or compose files may be created only when all of the following are true:

- Runtime folder has already been explicitly approved and created.
- Docker Desktop is installed and running.
- Repository mount policy is confirmed as read-only by default.
- Writable output path is limited and explicitly approved.
- `.env.example` contents are planned as placeholders only.
- Real `.env` is local-only and gitignored.
- No real secrets are included in Dockerfile, compose files, docs, prompts, or logs.
- The next task explicitly authorizes Dockerfile or compose creation.

If any condition is false, Dockerfile or compose files must not be created.

## MCP Creation Gate

MCP server code may be created only when all of the following are true:

- Docker/runtime boundary has been validated.
- Tool schema design and validation have passed.
- `safety_boundary_check` is the first tool implementation target.
- `app/` write protection is implemented or explicitly enforced by design.
- Secret redaction behavior is defined before any secret-capable runtime is used.
- Output directories are explicitly approved.
- MCP client connection approach is understood.
- The next task explicitly authorizes MCP server code creation.

If any condition is false, MCP server code must not be created.

## Stop Conditions

Stop immediately if:

- Docker is missing.
- Docker Desktop cannot start.
- Oracle access method is unclear.
- Git state is dirty and not understood.
- Any request asks to write secrets.
- Any request asks to commit secrets.
- Any request asks to paste real credentials into prompts.
- Any unexpected `app/` modification appears.
- Any request asks to make `app/` writable by default.
- Any request asks to skip `safety_boundary_check`.
- Any request asks to create runtime files without explicit implementation approval.

## Final Decision Format

Use this format before runtime implementation starts:

```text
Decision: READY / NOT READY

Blocking issues:
- ...

Non-blocking notes:
- ...

Approved next action:
- ...
```

Decision rules:

- Use `READY` only when every runtime, Docker, MCP, project, git, and secret gate is satisfied.
- Use `NOT READY` when any blocking issue exists.
- If the next action is not explicitly approved, the decision must be `NOT READY`.
- If secrets are unclear, the decision must be `NOT READY`.
- If `app/` write behavior is unclear, the decision must be `NOT READY`.
