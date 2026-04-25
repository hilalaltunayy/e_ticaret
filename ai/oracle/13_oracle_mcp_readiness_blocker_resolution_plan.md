# 13 Oracle MCP Readiness Blocker Resolution Plan

## Purpose

Define a safe, beginner-friendly plan to resolve the blockers found in `12_oracle_mcp_local_readiness_validation.md` before rerunning readiness validation.

This is a planning-only document. It does not create runtime files, Docker files, MCP code, scripts, automation, secrets, commits, or application changes.

## Current Decision From 12

Decision: `NOT READY`

Runtime creation is not allowed yet.

## Blocker 1: Git CLI Unavailable in Current Shell

### Why It Blocks Runtime Implementation

Git status must be verified before creating runtime files. Without Git CLI access, the current changed files cannot be reviewed, `app/` safety cannot be confirmed, and future implementation changes cannot be tracked safely.

### Safe Manual Check Commands

Run manually in a normal terminal:

```text
git --version
git status --short
git status --short app
```

### What NOT To Do

- Do not run `git reset`.
- Do not run `git checkout`.
- Do not run `git clean`.
- Do not stage files.
- Do not commit files.
- Do not discard changes.
- Do not modify `app/` to make status cleaner.

### Expected Safe Outcome

- `git --version` prints a Git version.
- `git status --short` prints current changed files or nothing if clean.
- `git status --short app` confirms whether `app/` has changes.
- Any dirty state is understood before implementation starts.

### Requires User Action or Codex Action?

- Requires user action first.
- Codex can rerun read-only validation after Git is available in the shell.

## Blocker 2: Missing Root `.gitignore`

### Why It Blocks Runtime Implementation

A root `.gitignore` is needed before local runtime files and local secrets are introduced. Without it, future `.env`, local Docker artifacts, logs, or generated outputs could be accidentally committed.

### Safe Manual Check Commands

Run manually:

```text
dir /a .gitignore
```

or:

```text
Get-ChildItem -Force .gitignore
```

### What NOT To Do

- Do not add broad ignore patterns without review.
- Do not ignore application source folders such as `app/`.
- Do not ignore KB or Oracle planning docs unintentionally.
- Do not commit `.gitignore` until its contents are reviewed.

### Expected Safe Outcome

- A reviewed `.gitignore` plan exists before implementation.
- Future `.env`, `.env.local`, local Docker secrets, and temporary runtime outputs are protected.

### Requires User Action or Codex Action?

- Requires user approval before Codex edits `.gitignore`.
- Codex can recommend `.gitignore` entries, but this task does not perform the edit.

## Blocker 3: Root `.env` Exists While `.gitignore` Is Missing

### Why It Blocks Runtime Implementation

A root `.env` may contain real credentials. If `.gitignore` is missing, there is a risk that local secrets could be committed later. The `.env` file must be treated as sensitive and local-only.

### Safe Manual Check Commands

Run manually without printing file contents:

```text
dir /a .env
git status --short .env
```

PowerShell alternative:

```text
Test-Path .env
git status --short .env
```

### What NOT To Do

- Do not print `.env` contents.
- Do not paste `.env` contents into prompts.
- Do not edit `.env`.
- Do not delete `.env`.
- Do not commit `.env`.
- Do not copy secrets into documentation.

### Expected Safe Outcome

- `.env` is confirmed as local-only.
- `.env` is protected by `.gitignore` before runtime implementation.
- No secret values are exposed in logs, prompts, reports, or commits.

### Requires User Action or Codex Action?

- Requires user action to confirm `.env` is local-only without exposing values.
- Codex can later add `.gitignore` protection only if explicitly approved.

## Blocker 4: Docker Local Config Access Warning

### Why It Blocks Runtime Implementation

Docker appears installed, but Docker reported an access warning for local Docker config. Runtime implementation should not start until Docker Desktop and local Docker configuration are healthy enough to run predictable local containers.

### Safe Manual Check Commands

Run manually:

```text
docker --version
docker compose version
docker info
```

### What NOT To Do

- Do not start Oracle containers yet.
- Do not build Oracle images yet.
- Do not create Dockerfiles yet.
- Do not store Docker credentials in the repository.
- Do not commit Docker config files.
- Do not bypass permission warnings without understanding them.

### Expected Safe Outcome

- Docker version command works without unexpected errors.
- Docker Compose version command works.
- `docker info` confirms Docker Desktop is running.
- Any local Docker config permission warning is understood or resolved.

### Requires User Action or Codex Action?

- Requires user action first because the warning is tied to the local user Docker configuration.
- Codex can rerun read-only Docker checks after the local environment is fixed.

## Blocker 5: `app/` Change Status Cannot Be Verified Until Git Works

### Why It Blocks Runtime Implementation

Oracle runtime implementation must not accidentally mix with application changes. Since Git CLI was unavailable, `app/` change status could not be confirmed.

### Safe Manual Check Commands

Run manually after Git works:

```text
git status --short app
```

Optional broader read-only check:

```text
git status --short
```

### What NOT To Do

- Do not modify `app/`.
- Do not refactor `app/`.
- Do not format `app/`.
- Do not rename `app/` files.
- Do not move `app/` files.
- Do not delete `app/` files.
- Do not reset, checkout, or discard `app/` changes.
- Do not stage or commit anything as part of this readiness work.

### Expected Safe Outcome

- `git status --short app` is empty, or any listed changes are understood and intentionally unrelated.
- Runtime implementation does not proceed while unexpected `app/` changes are unresolved.

### Requires User Action or Codex Action?

- Requires Git CLI availability first.
- Codex can rerun read-only validation after Git works.

## Recommended Next Safe Action

1. Make Git CLI available in the shell.
2. Run read-only Git status checks.
3. Review or create a `.gitignore` plan that protects `.env`, `.env.local`, and local secret files.
4. Confirm root `.env` is local-only without printing its contents.
5. Review Docker Desktop local config warning.
6. Rerun `12_oracle_mcp_local_readiness_validation.md` style readiness validation.

## Runtime Creation Allowed?

NO.

Runtime creation remains blocked until the readiness blockers are resolved and a new readiness validation returns `READY`.
