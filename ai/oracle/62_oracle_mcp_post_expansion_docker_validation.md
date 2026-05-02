# 62 Oracle MCP Post-Expansion Docker Validation

## Purpose

Validate the current Oracle MCP runtime after recent read-only tool additions.

This is a controlled static validation. No code was modified, Docker was not run, `app/` was not touched, Domain KB was not changed, no tools were added, and no Git commit was created.

## Scope

Reviewed paths:

- `ai/oracle/runtime/`
- `ai/oracle/runtime/tools/`
- `ai/oracle/runtime/registry.py`
- `ai/oracle/runtime/server.py`
- `ai/oracle/runtime/README.md`
- `ai/oracle/runtime/compose.yaml`
- `ai/oracle/runtime/Dockerfile`

## Current Runtime Structure Map

| Path | Role | Status | Notes |
|------|------|--------|------|
| `ai/oracle/runtime/README.md` | Runtime documentation | Present | Lists runtime files, safety boundaries, run command, and expected output. |
| `ai/oracle/runtime/Dockerfile` | Local runtime image definition | Present | Uses Python Alpine, non-root user, `/workspace`, and `server.py` command. |
| `ai/oracle/runtime/compose.yaml` | Local Compose definition | Present | Read-only repository mount and output-only writable mount. |
| `ai/oracle/runtime/config.py` | Runtime path config | Present | Non-secret configuration file. |
| `ai/oracle/runtime/registry.py` | Tool registry | Present | Registers six implemented tools. |
| `ai/oracle/runtime/server.py` | Placeholder runtime entrypoint | Present | Imports registry/tools, prints registered tools, runs safe samples, exits. |
| `ai/oracle/runtime/output/.gitkeep` | Writable output placeholder | Present | Only approved writable runtime directory marker. |
| `ai/oracle/runtime/tools/__init__.py` | Python package marker | Present | Required for tool imports. |
| `ai/oracle/runtime/tools/repo_file_lookup.py` | File path lookup tool | Present / implemented | Read-only path metadata lookup. |
| `ai/oracle/runtime/tools/route_lookup.py` | Route lookup tool | Present / implemented | Read-only `Routes.php` text parser. |
| `ai/oracle/runtime/tools/model_lookup.py` | Model/schema lookup tool | Present / implemented | Read-only model, migration, and seeder text parser. |
| `ai/oracle/runtime/tools/controller_lookup.py` | Controller lookup tool | Present / implemented | Read-only controller and route handler text parser. |
| `ai/oracle/runtime/tools/permission_lookup.py` | Permission lookup tool | Present / implemented | Read-only RBAC source text parser. |
| `ai/oracle/runtime/tools/filter_lookup.py` | Filter lookup tool | Present / implemented | Read-only filter/config/route source text parser. |
| `ai/oracle/runtime/*_implementation.md` | Tool implementation reports | Present | Documents route, permission, filter, controller, and model tool implementation. |

## Implemented Tools

| Tool | Registry Status | Tool File | Can Inspect | Static Status |
|------|-----------------|-----------|-------------|---------------|
| `repo_file_lookup` | `implemented` | `tools/repo_file_lookup.py` | Repository file paths, extensions, limited path metadata | Pass |
| `route_lookup` | `implemented` | `tools/route_lookup.py` | `app/Config/Routes.php` route definitions | Pass |
| `model_lookup` | `implemented` | `tools/model_lookup.py` | `app/Models/`, `app/Database/Migrations/`, `app/Database/Seeds/` | Pass |
| `controller_lookup` | `implemented` | `tools/controller_lookup.py` | `app/Controllers/`, `app/Config/Routes.php` | Pass |
| `permission_lookup` | `implemented` | `tools/permission_lookup.py` | approved RBAC/auth source files | Pass |
| `filter_lookup` | `implemented` | `tools/filter_lookup.py` | `app/Config/Filters.php`, `app/Config/Routes.php`, `app/Filters/` | Pass |

## Registry Consistency

| Check | Result | Notes |
|------|--------|------|
| All expected tools registered | Pass | Six expected tools are present in `INITIAL_TOOLS`. |
| All registered statuses current | Pass | All six tools are marked `implemented`; no stale placeholder status remains. |
| Handler paths present | Pass | Each registered tool has a handler pointing to an existing tool module/function. |
| Duplicate names | Pass | No duplicate tool names were found in the static registry review. |
| Missing imports in `server.py` | Pass | `server.py` imports all six implemented tool functions. |
| Registry/server mismatch | Pass | Tools listed in registry are represented in server sample execution path. |

## Runtime Boot Path Review

| Check | Result | Notes |
|------|--------|------|
| Entrypoint command | Pass | Dockerfile command runs `python ai/oracle/runtime/server.py`. |
| Registry loading | Pass | `server.py` imports `get_registered_tools()` from `registry.py`. |
| Tool imports | Pass | `server.py` imports all six implemented tools. |
| Sample execution path | Pass | Server performs bounded sample lookups for each implemented tool and exits. |
| Persistent service behavior | Pass | No long-running server loop or port listener exists. |
| Real MCP protocol | Warning | Not implemented yet by design. Future protocol upgrade needs a new boundary review. |

## Docker Readiness Review

| Check | Result | Notes |
|------|--------|------|
| Compose service name | Pass | Service is `oracle-mcp-runtime`. |
| Container name | Pass | `container_name: oracle-mcp-runtime`. |
| Build context | Pass | Uses runtime-local `Dockerfile`. |
| Repository mount | Pass | `../../..:/workspace:ro` keeps project mount read-only. |
| Writable mount | Pass | Only `./output:/workspace/ai/oracle/runtime/output:rw` is writable. |
| `read_only` container flag | Pass | `read_only: true` is set. |
| Temporary filesystem | Pass | `tmpfs: /tmp` is present. |
| Ports | Pass | No `ports` section exists. |
| Host networking | Pass | No `network_mode: host`. |
| Privileged mode | Pass | No `privileged` key exists. |
| Secrets/env file | Pass | No `env_file`, Compose `secrets`, or `.env` mount is defined. |
| Dockerfile base | Pass | Uses `python:3.12-alpine`. |
| Non-root user | Pass | Creates and uses non-root `oracle` user. |
| App write capability | Pass | Compose mount is read-only and runtime tools do not write app files. |
| Current Docker execution validation | Warning | Docker was not run for this report. A controlled rebuild/run is still recommended after current tool expansion. |

## Safety Boundary Review

| Boundary | Result | Notes |
|----------|--------|------|
| Read-only intent | Pass | Tool docs and implementations use text inspection and metadata lookup only. |
| No app write capability | Pass | No write operations were found in reviewed tool paths; Compose repository mount is read-only. |
| No DB access | Pass | No database connection logic was found in runtime files. |
| No `.env` exposure | Pass | Compose does not mount `.env`; lookup tools block or avoid secret-like files. |
| No secret dependency | Pass | Runtime has no API key, wallet, credential, or provider secret requirement. |
| No ports | Pass | No runtime port exposure or listener exists. |
| Result limits | Pass | Lookup tools use default and hard maximum result limits. |
| Path traversal protection | Warning | Tools accept `repo_root` as a parameter. In current placeholder server this is controlled by `Path.cwd()`, but future real MCP tool schemas should not allow arbitrary repo root overrides from external callers. |
| Heavy/unsafe folder skipping | Pass with note | Repo/model/controller tools skip heavy or unsafe folders. Some fixed-source tools do not require recursive folder skipping because they inspect explicit approved files. |

## Gaps Before Real MCP Protocol Upgrade

- Real MCP protocol server is not implemented.
- Tool input schemas are not enforced by a protocol layer yet.
- External callers must not be allowed to pass arbitrary `repo_root` values in a future MCP implementation.
- Output schemas should be normalized across all tools before protocol exposure.
- Tool errors should be standardized into a common `status`, `warnings`, `sources`, and `needs_review` shape.
- Docker rebuild/run validation should be repeated after the latest tool additions.
- Runtime output is currently sample/demo-oriented; real MCP mode should separate tool execution from boot diagnostics.
- No authentication/authorization model exists for MCP clients yet.
- No task/GitNexus integration exists yet.
- No Domain KB update automation is connected to Oracle runtime yet.

## Pass / Warning / Fail Summary

| Area | Status | Notes |
|------|--------|------|
| Runtime structure | Pass | Expected runtime files and tool modules exist. |
| Implemented tool list | Pass | Six expected tools are implemented. |
| Registry consistency | Pass | Registry, statuses, and handlers are coherent. |
| Server boot path | Pass | Static boot path is coherent and bounded. |
| Docker/Compose static safety | Pass | Read-only repo, output-only writable mount, no ports, no env/secrets, no privileged mode. |
| Runtime safety boundaries | Pass with warnings | Read-only behavior is preserved; future MCP must lock repo root and normalize schemas. |
| Current Docker execution validation | Warning | Not executed in this report; controlled rebuild/run is the next validation step. |
| Real MCP readiness | Warning | Runtime is not a real MCP protocol server yet. |

## Recommended Next Milestone

Recommended next milestone:

```text
Controlled Docker rebuild/run validation for the current post-expansion runtime.
```

Suggested next validation scope:

- Build only: `docker compose build oracle-mcp-runtime`
- Run only after build approval: `docker compose run --rm oracle-mcp-runtime`
- Confirm all six tools print as `implemented`.
- Confirm sample lookups complete.
- Confirm no persistent container remains.
- Confirm no ports, secrets, env files, or app writes are introduced.

After that passes:

```text
GitNexus MCP integration review
```

Reason:

- Oracle runtime now has enough read-only repo evidence capability to support task/path/domain guidance.
- GitNexus is still contract-only and has no `ai/gitnexus/` implementation folder.
- GitNexus should be the next coordination layer before Orkestra or CI gates.

## Final Decision

Oracle MCP Post-Expansion Validation Complete: YES
