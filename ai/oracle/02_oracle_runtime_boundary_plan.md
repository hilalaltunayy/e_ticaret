# 02 Oracle Runtime Boundary Plan

## Purpose

Define the runtime boundary for the future local Docker-based Oracle MCP.

## Runtime Goal

Oracle MCP should run locally and provide repository-aware guidance without directly modifying application code.

## Execution Environment

- Local machine
- Docker container in a future phase
- Mounted repository path
- Read-only access by default
- Optional write access only to `ai/oracle` reports or explicitly allowed documentation directories

## Filesystem Access Policy

| Area | Access | Reason | Notes |
|------|--------|--------|------|
| `app/` | Read-only | Oracle needs repository guidance context but must not modify application code. | No app writes in guide mode, planning mode, or default MCP mode. |
| `ai/domain-kb/` | Read-only by default | Domain KB is the source of truth for domain and KB mapping. | Write only when explicitly running an approved KB update skill. |
| `ai/oracle/` | Writable for Oracle reports/plans | Oracle planning, validation, and report files belong here. | No runtime secrets should be stored here. |
| `ai/gitnexus/` | Read/write only when GitNexus planning explicitly allows it | Future GitNexus implementation planning lives outside Domain KB. | Oracle should not become the GitNexus task store. |
| `.env` | No access by default | Real secrets may exist here. | Must not be read unless explicitly authorized for a specific runtime task. |
| `.env.example` | Read-only | Template configuration may document required environment variables. | Placeholders only; no real secrets. |
| `vendor/` | Read-only | Dependency inspection may be useful for repository guidance. | No dependency modification. |
| `writable/` | Read-only or no access by default | May contain runtime-generated files, logs, cache, uploads, or sensitive local state. | Avoid logging or copying runtime contents. |
| `public/` | Read-only | Public assets and uploads may affect UI/media guidance. | Do not modify assets or uploads in Oracle guide mode. |

Rules:

- `app/` is read-only.
- `ai/domain-kb/` is read-only by default and writable only when explicitly running an approved KB update skill.
- `ai/oracle/` is writable for Oracle reports and plans.
- Real `.env` must not be read unless explicitly allowed.
- `.env.example` may be read.
- Secrets must not be logged.

## Secret Boundary

- No real secrets in repository.
- No secrets in KB.
- No secrets in Oracle reports.
- No secrets in GitNexus metadata.
- MCP runtime may receive secrets only through environment variables or local Docker secrets.
- Secrets must be redacted in logs and outputs.
- Oracle should refuse to reproduce detected secrets and should report only that a secret-like value was found.

## MCP Tool Boundary

| Tool | Purpose | Access Level | Can Write? | Notes |
|------|---------|--------------|------------|------|
| `repo_lookup` | Locate repository files, classes, controllers, models, views, and services. | read-only repo | No | Must not modify app or config files. |
| `domain_lookup` | Find domain ownership and related KB evidence. | KB read | No | Uses Domain KB as source of truth. |
| `route_lookup` | Inspect route-related KB and repository route files. | read-only repo; KB read | No | May read `app/Config/Routes.php`; no route writes. |
| `permission_lookup` | Inspect filters, permissions, and RBAC evidence. | read-only repo; KB read | No | May read `app/Config/Filters.php` and related KB. |
| `kb_impact_check` | Map changed paths to domains and KB files. | KB read | No | Uses `ai/domain-kb/kb-manifest.yaml`. |
| `task_create` | Draft GitNexus task metadata. | task draft write | Yes, future only | Should write drafts outside app code, likely under future `ai/gitnexus/`. |
| `plan_create` | Draft plan steps from task metadata. | report write; task draft write | Yes, future only | Should not modify application code. |
| `validation_check` | Produce validation notes or reports. | KB read; report write | Yes, report only | Writes only approved report locations. |
| `kb_update_required_check` | Decide whether KB update is required. | KB read | No | Advisory unless an approved KB update skill is explicitly invoked. |

## Read / Write Modes

- `guide_mode`: read-only.
  - Reads repository and KB evidence.
  - Answers questions and provides guidance.
  - Does not write files.
- `planning_mode`: writes only `ai/oracle` plan/report docs.
  - Creates Oracle planning or validation reports.
  - Does not write app code, Docker files, MCP code, or automation scripts.
- `kb_assist_mode`: suggests KB update, does not write unless explicitly delegated.
  - Identifies affected domains and KB files.
  - Recommends KB update reports or claim updates.
  - Requires explicit authorization before writing KB documentation.
- `future_automation_mode`: requires stricter approval.
  - May run MCP tools with declared access boundaries.
  - Must preserve app read-only access unless a separate authorized implementation policy says otherwise.
  - Must log mode, requested access, and output location.

## Docker Boundary

- Docker will isolate Oracle runtime.
- Repo can be mounted read-only initially.
- Writable mount may be limited to `ai/oracle/outputs`.
- Real secrets should enter only through environment variables or local Docker secrets.
- Docker runtime must not bake secrets into images.
- No Docker implementation is included in this document.
- No Dockerfile is created by this document.

## Failure Conditions

- Tool attempts to write `app/`.
- Tool tries to read real `.env` without approval.
- Secret appears in output.
- Oracle suggests code change without citing KB/repo evidence.
- MCP tool exceeds declared access boundary.
- Tool writes outside approved report or documentation directories.
- Tool logs raw sensitive runtime files from `writable/`.

## First Runtime Recommendation

- Start with read-only Docker mount.
- Allow report writing only after validation.
- Keep `app/` read-only.
- Keep secrets outside repo.
- Prefer `guide_mode` first.
- Add `planning_mode` only after this boundary is validated.
- Defer `future_automation_mode` until MCP tool schemas and approval rules are validated.

## Final Summary

This boundary is ready for validation.

The future Oracle MCP should begin as a local, read-only repository guide with optional report writing under tightly scoped directories. Docker, MCP tools, write modes, and secret handling must remain governed by explicit boundaries before implementation starts.
