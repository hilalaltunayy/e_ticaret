# 18 Oracle MCP Runtime Folder Creation Plan

## Purpose

Define the safe plan for creating the first Oracle MCP runtime folder in a later explicit implementation task.

This document is planning-only. It does not create `ai/oracle/runtime/`, Docker files, MCP code, scripts, automation, secrets, or application changes.

## Preconditions Confirmed From 17

From `17_oracle_mcp_post_commit_readiness_validation.md`:

- Final decision is `READY`.
- `.gitignore` exists and protects `.env`.
- `.env` is no longer tracked by Git, according to user-confirmed checks.
- Local `.env` still exists, and its contents were not read.
- `app/` has no unexpected changes, according to user-confirmed checks.
- `ai/oracle` docs `07` through `16` exist.
- `ai/oracle/runtime/` does not exist.
- Docker CLI and Docker Compose are available.
- `docker info` works in the user-confirmed terminal.
- Docker seccomp warning remains as a non-blocking note for runtime folder planning.
- No runtime, Docker, MCP, script, automation, or secret files were created.

## Why Runtime Folder Can Be the Next Implementation Step

Runtime folder creation can be the next implementation step because the previous readiness blockers were resolved:

- `.env` is protected by `.gitignore`.
- `.env` is no longer tracked by Git.
- `app/` is clean and must remain untouched.
- Oracle planning and validation docs are complete enough to define the runtime boundary.
- Runtime folder creation is narrow, reversible by review, and does not require Dockerfile, compose, MCP code, scripts, or secrets.

The next implementation step must remain intentionally small.

## What Will Be Created in the Next Explicit Task

Only these items may be created in the next explicit task:

- `ai/oracle/runtime/`
- `ai/oracle/runtime/README.md`

The README should explain:

- The runtime folder is a future local Oracle MCP runtime area.
- No secrets belong in this folder.
- `app/` remains read-only.
- Dockerfile, compose, and MCP code are not created yet.
- Docker seccomp warning must be reviewed before Dockerfile/compose hardening.

## What Must NOT Be Created Yet

Do not create:

- `Dockerfile`
- `docker-compose.yml`
- MCP server code
- `.env`
- `.env.local`
- scripts
- automation
- Oracle wallet files
- Oracle credentials
- API keys
- private keys
- generated runtime outputs

## app/ Protection Rules

- Do not modify `app/`.
- Do not refactor `app/`.
- Do not format `app/`.
- Do not rename `app/` files.
- Do not move `app/` files.
- Do not delete `app/` files.
- Do not update controllers, models, services, views, filters, routes, migrations, seeders, config files, or public assets.
- Runtime folder creation must be limited to `ai/oracle/runtime/`.

## Secret Protection Rules

- Do not read `.env`.
- Do not print `.env`.
- Do not edit `.env`.
- Do not delete `.env`.
- Do not overwrite `.env`.
- Do not create any new secret file.
- Do not create Oracle wallet files.
- Do not store credentials in README files.
- Do not paste secrets into prompts.
- Any future `.env.example` must contain placeholders only, but `.env.example` is not part of the next runtime folder creation task.

## Docker Seccomp Warning Note

Docker seccomp warning remains:

```text
WARNING: daemon is not using the default seccomp profile
```

Decision:

- This warning does not block creating the empty runtime folder and README.
- This warning must be reviewed before Dockerfile or compose hardening.
- Dockerfile and compose creation must remain blocked until a later explicit task.

## Validation After Folder Creation

After the next explicit folder creation task, validate:

- `ai/oracle/runtime/` exists.
- `ai/oracle/runtime/README.md` exists.
- No Dockerfile exists.
- No `docker-compose.yml` exists.
- No MCP server code exists.
- No `.env` or secret file was created under `ai/oracle/runtime/`.
- `app/` remains untouched.
- `.env` contents were not read or printed.
- No git staging, commit, reset, checkout, clean, or discard operation was performed unless explicitly requested in a separate task.

## Final Decision

Is it safe to create runtime folder in the next explicit task? YES

Blocking issues:

- None for creating only `ai/oracle/runtime/` and `ai/oracle/runtime/README.md`.

Non-blocking notes:

- Docker seccomp warning remains and must be reviewed before Dockerfile or compose hardening.
- Docker implementation remains out of scope for the next folder-only task.
- MCP implementation remains out of scope for the next folder-only task.
- Secret creation remains out of scope for the next folder-only task.

Approved next action:

- In the next separate explicit task, create only `ai/oracle/runtime/` and `ai/oracle/runtime/README.md`.
