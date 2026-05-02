# 53 Oracle MCP Runtime Stabilization Review

## Purpose

Review the current Oracle MCP runtime baseline under `ai/oracle/runtime/` before declaring it complete.

This review is read-only. It does not modify `app/`, production project code, runtime code, Docker files, `.env`, or secrets. It does not implement new tools.

## Runtime Inventory

Current runtime files reviewed:

- `ai/oracle/runtime/Dockerfile`
- `ai/oracle/runtime/compose.yaml`
- `ai/oracle/runtime/README.md`
- `ai/oracle/runtime/config.py`
- `ai/oracle/runtime/registry.py`
- `ai/oracle/runtime/server.py`
- `ai/oracle/runtime/tools/repo_file_lookup.py`
- `ai/oracle/runtime/tools/route_lookup.py`
- `ai/oracle/runtime/tools/permission_lookup.py`
- `ai/oracle/runtime/tools/filter_lookup.py`
- `ai/oracle/runtime/tools/__init__.py`
- `ai/oracle/runtime/output/.gitkeep`

## Stabilization Checklist

| Item | Result | Evidence | Notes |
|------|--------|----------|-------|
| 1. Registered tools and implemented/placeholder status | PASS | `registry.py` lists `repo_file_lookup`, `route_lookup`, `permission_lookup`, and `filter_lookup` as `implemented`; `model_lookup` and `controller_lookup` remain `placeholder`. | Tool status is explicit and stable. |
| 2. `server.py` output matches `registry.py` | PASS | Local `python ai/oracle/runtime/server.py` output printed all registered tools with the same statuses shown in `registry.py`. | Runtime entrypoint is consistent with registry metadata. |
| 3. Docker run remains safe: no ports | PASS | `compose.yaml` has no `ports` entry. | No port exposure configured. |
| 3. Docker run remains safe: no `env_file` | PASS | `compose.yaml` has no `env_file` entry. | No environment file is mounted. |
| 3. Docker run remains safe: no secrets | PASS | `compose.yaml` has no `secrets` entry; runtime scan found no secret reads, only secret-blocking patterns in `repo_file_lookup.py`. | `.env` and secret-like paths are blocked by path lookup. |
| 3. Docker run remains safe: no privileged mode | PASS | `compose.yaml` has no `privileged` entry and no `network_mode: host`. | Container boundary remains conservative. |
| 3. Docker run remains safe: no app modifications | PASS | `compose.yaml` mounts the repository as `../../..:/workspace:ro` and only `./output` as writable. | Runtime cannot write to the mounted repository except approved output path. |
| 4. Tools are read-only | PASS | Tool files inspect path/text data only. No write/delete/rename patterns were found in runtime tool code. | The current tools are lookup/reporting tools only. |
| 5. Previous app-side RBAC/CSRF experiment reverted or out of MCP scope | PASS | `app/Config/Filters.php:89` is back to `// 'csrf',`; `manage_dashboard` no longer appears in `InitialAuthSeeder.php`; revert documented in `52_revert_rbac_csrf_gap_fix.md`. | App-side experiment is outside the MCP runtime baseline and has been reverted. |
| 6. Remaining work before baseline complete | PASS WITH NOTES | Implemented runtime has four read-only tools; two planned tools remain placeholders. | Baseline can be complete if placeholders are accepted as intentionally out of scope for this milestone. |

## Registered Tool State

Implemented:

- `repo_file_lookup`
- `route_lookup`
- `permission_lookup`
- `filter_lookup`

Placeholders:

- `model_lookup`
- `controller_lookup`

## Runtime Safety Notes

- Docker command remains the controlled one-shot pattern: `docker compose run --rm oracle-mcp-runtime`.
- `docker compose up` should remain out of scope until a persistent MCP lifecycle is planned.
- No real MCP protocol server is implemented yet.
- No database access is implemented.
- No `.env` or secret reading is implemented.
- No ports are exposed.
- `app/` remains read-only through Docker mount policy.

## Risks

- `model_lookup` and `controller_lookup` are still placeholders, so baseline should be described as a lookup baseline, not a full Oracle MCP coverage baseline.
- `permission_lookup` and `filter_lookup` are static text inspection tools; they do not prove live runtime access behavior.
- `repo_file_lookup` can discover paths but intentionally does not read file contents.
- Docker image may need rebuild after runtime code changes before Docker output matches local Python output.
- App-side RBAC/CSRF findings remain application concerns, not MCP runtime blockers.

## Next Recommended Step

Create a baseline completion report that freezes the current Oracle MCP runtime milestone as:

- Local Docker runtime works.
- Runtime remains read-only.
- Four tools are implemented.
- Two tools are intentionally deferred.
- App-side RBAC/CSRF work is reverted and separated from MCP baseline.

After that, choose one of two paths:

- Implement `model_lookup` as the next read-only tool.
- Pause tool implementation and validate current runtime through Docker rebuild/run.

## Final Decision

Ready to complete MCP baseline? YES
