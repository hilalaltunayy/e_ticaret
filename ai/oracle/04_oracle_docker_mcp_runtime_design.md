# 04 Oracle Docker MCP Runtime Design

## Purpose

Design the future local Docker-based Oracle MCP runtime without implementing it yet.

## Runtime Architecture

- Oracle MCP runs locally.
- Docker provides isolation.
- Repository is mounted read-only by default.
- Oracle reads Domain KB and repository files.
- Oracle writes only Oracle reports/plans when explicitly allowed.
- Oracle uses `ai/domain-kb/kb-manifest.yaml` to map paths to domains and KB files.
- Oracle must not modify application code.

## Docker Runtime Model

- Container name: `oracle-mcp-local`
- Mounted repository path: `/workspace/repo:ro`
- Optional writable output mount: `/workspace/repo/ai/oracle/outputs:rw`
- Environment variables provide runtime configuration.
- No real secrets may be stored in the repository.
- The default runtime should start with read-only repository access.
- Writable output access should be limited to Oracle reports and plans after validation.

## Environment Variables

| Variable | Purpose | Required? | Secret? | Example |
|---------|---------|-----------|---------|---------|
| `ORACLE_MODE` | Selects runtime mode. | Yes | No | `guide_mode` |
| `ORACLE_REPO_ROOT` | Repository root path inside the container. | Yes | No | `/workspace/repo` |
| `ORACLE_OUTPUT_DIR` | Directory for Oracle reports and plans. | Required when writing reports | No | `/workspace/repo/ai/oracle/outputs` |
| `ORACLE_ALLOWED_WRITE_DIRS` | Comma-separated list of allowed write directories. | Required when writes are enabled | No | `/workspace/repo/ai/oracle/outputs` |
| `ORACLE_LOG_LEVEL` | Runtime logging level. | No | No | `info` |
| `ORACLE_AI_PROVIDER` | AI provider name for future model access. | Future only | No | `openai` |
| `ORACLE_AI_MODEL` | AI model name for future model access. | Future only | No | `gpt-5.4` |
| `ORACLE_API_KEY` | API key for future AI provider access. | Future only | Yes | `placeholder-only` |

Rules:

- `ORACLE_API_KEY` is secret.
- Use `.env.local` or Docker secrets for real secrets.
- Do not commit real values.
- `.env.example` may contain placeholders only.
- Tools must not print, log, or store secret values.

## MCP Tool Set

| Tool | Input | Output | Access | Writes? |
|------|-------|--------|--------|--------|
| `repo_lookup` | repo path, symbol, class, file name, or feature hint | matching paths and source references | read-only repo | No |
| `domain_lookup` | domain name or feature hint | domain summary and KB source files | KB read | No |
| `route_lookup` | route URI, controller, method, or domain | route evidence and related KB rows | read-only repo; KB read | No |
| `permission_lookup` | permission name, role, filter, route, or domain | RBAC and permission evidence | read-only repo; KB read | No |
| `kb_impact_check` | changed paths | affected domains, impact levels, and KB files | KB read | No |
| `task_draft_create` | user request, domain context, risk level | draft GitNexus task metadata | task draft write | Yes, future report/draft write only |
| `plan_draft_create` | task draft, affected domains, risk notes | draft plan steps and KB impact | report write; task draft write | Yes, future report/draft write only |
| `validation_check` | task metadata, changed paths, KB evidence | validation findings and report draft | KB read; report write | Yes, report only |
| `kb_update_required_check` | changed paths, task type, risk level | KB update required decision and reasons | KB read | No |
| `safety_boundary_check` | requested tool, mode, paths, write intent | allow/deny decision with reason | policy read | No |

## Tool Behavior Rules

- Tools must cite source files or KB files.
- Tools must not invent repository facts.
- Tools must not modify `app/`.
- Tools must redact secrets.
- Tools must respect `ORACLE_MODE`.
- Tools must return `Needs Review` when evidence is unclear.
- Tools must check declared access before reading or writing.
- Tools must not read real `.env` unless explicitly authorized by a future approved policy.
- Tools that write reports must write only to allowed output directories.

## Oracle Modes

| Mode | Allowed Reads | Allowed Writes | Expected Outputs | Prohibited Actions |
|------|---------------|----------------|------------------|--------------------|
| `guide_mode` | Repository files, Domain KB, Oracle plans, GitNexus contract docs | None | Repository guidance, domain mapping, source references, risk notes | File writes, app modifications, secret reads, automation execution |
| `planning_mode` | Repository files, Domain KB, Oracle plans, GitNexus contract docs | `ai/oracle` plan/report docs only | Planning docs, runtime design docs, validation reports | App writes, Dockerfile creation, MCP code creation, secret creation |
| `validation_mode` | Repository files, Domain KB, Oracle plans, GitNexus contract docs | `ai/oracle` validation reports only | Validation checklist, risks, readiness verdict | App writes, code generation, secret reads, runtime execution |
| `kb_assist_mode` | Repository files and Domain KB | None by default; KB writes only if explicitly delegated to an approved KB update skill | KB impact decisions, suggested KB updates, claim impact notes | Silent KB writes, app writes, unsupported claims |

## Local Docker Usage for Beginner

- Docker image:
  - A packaged runtime that contains everything needed to run Oracle MCP later.
- Container:
  - A running instance of the Docker image.
- Volume mount:
  - A way to give the container access to local project files.
- Read-only mount:
  - A mount where the container can read files but cannot change them.
- Environment variable:
  - Runtime configuration passed from the local machine into the container.

No executable Docker commands are included in this design document.

## Security Design

- Repository default read-only.
- App code never writable.
- Secrets never stored in repo.
- Secrets never logged.
- Output writes limited to `ai/oracle/outputs`.
- Tool access must be checked before execution.
- Real `.env` files are not read by default.
- `.env.example` can contain placeholders only.
- Secret-like values in output must be redacted.
- Any boundary violation should return a failure result instead of attempting recovery silently.

## First Implementation Path

1. Create `.env.example` later.
2. Create Dockerfile later.
3. Create minimal MCP server later.
4. Implement read-only repo lookup first.
5. Add KB-aware lookup.
6. Add task/plan draft tools.
7. Add validation tools.
8. Test with controlled queries.

## Out of Scope

- Dockerfile implementation
- MCP server implementation
- Real AI provider integration
- Secret creation
- GitNexus implementation
- Orchestrator implementation
- App code changes

## Final Summary

This design is ready for validation.

It defines a future local Docker-based Oracle MCP runtime with read-only repository access by default, strict secret boundaries, scoped output writes, declared tool access levels, and clear mode-based behavior. It does not implement Docker, MCP tools, scripts, secrets, automation, or application code changes.
