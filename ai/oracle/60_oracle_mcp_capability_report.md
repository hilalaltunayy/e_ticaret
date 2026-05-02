# 60 Oracle MCP Capability Report

## Purpose

Summarize the completed Oracle MCP runtime expansion and define the current capability baseline before moving to a larger orchestration milestone.

This report is documentation-only. It does not modify code, run Docker, modify `app/`, modify Domain KB, or create a Git commit.

## Implemented Tools

| Tool | Status | Access Level | What It Can Inspect | Source Evidence |
|------|--------|--------------|---------------------|-----------------|
| `repo_file_lookup` | Implemented | `read_only_repo` | Repository file paths by filename or partial path; returns relative path metadata only | `ai/oracle/runtime/registry.py`, `ai/oracle/runtime/tools/repo_file_lookup.py` |
| `route_lookup` | Implemented | `read_only_repo` | CodeIgniter route definitions, route paths, HTTP methods, controller targets, and source lines | `ai/oracle/runtime/registry.py`, `ai/oracle/runtime/tools/route_lookup.py` |
| `model_lookup` | Implemented | `read_only_repo` | Models, migrations, seeders, table names, allowed fields, validation rules, return types, soft-delete hints, UUID hints, migration fields, and seeder references | `ai/oracle/runtime/registry.py`, `ai/oracle/runtime/tools/model_lookup.py` |
| `controller_lookup` | Implemented | `read_only_repo` | Controller classes, methods, route handlers, redirects, service/model references, view returns, and permission/session hints | `ai/oracle/runtime/registry.py`, `ai/oracle/runtime/tools/controller_lookup.py` |
| `permission_lookup` | Implemented | `read_only_repo` | RBAC route/filter/permission/role/service/model references in approved auth-related sources | `ai/oracle/runtime/registry.py`, `ai/oracle/runtime/tools/permission_lookup.py` |
| `filter_lookup` | Implemented | `read_only_repo` | CodeIgniter filter aliases, route filters, global filter configuration, filter classes, before/after logic, roles, permissions, and CSRF references | `ai/oracle/runtime/registry.py`, `ai/oracle/runtime/tools/filter_lookup.py` |

## Capability Summary

### Repository File Lookup

- Searches repository file paths.
- Returns relative paths and file metadata.
- Does not read file contents.
- Skips unsafe or heavy folders.
- Blocks `.env` and secret-like filenames.

### Route Lookup

- Reads `app/Config/Routes.php` as text.
- Searches route path, controller name, and keywords.
- Returns route path, HTTP method when detectable, controller target, source file, and line number.
- Does not execute CodeIgniter or PHP.

### Model Lookup

- Reads `app/Models/`, `app/Database/Migrations/`, and `app/Database/Seeds/` as text.
- Searches model classes, table names, field names, and model keywords.
- Helps inspect `UserModel`, `ProductsModel`, `OrderModel`, `RolePermissionModel`, table names, `allowedFields`, `validationRules`, `returnType`, `useSoftDeletes`, `primaryKey`, and `uuid`.
- Returns source-anchored evidence with detected type, related model/table/field, migration/seeder hints, confidence, and risk hints.
- Does not instantiate models, run migrations, run seeders, or access the database.

### Controller Lookup

- Reads `app/Controllers/` and `app/Config/Routes.php` as text.
- Searches controller class names, methods, route paths, and behavior keywords.
- Helps trace routes such as `admin/dashboard`, `admin/orders`, and `login` to controller evidence.
- Detects controller classes, methods, route handlers, redirects, service/model references, view returns, and permission/session hints.
- Does not execute controllers or PHP.

### Permission Lookup

- Reads approved RBAC and auth-related source files as text.
- Searches route paths, permission codes, role names, and module keywords.
- Helps inspect permissions such as `manage_orders`, `manage_reviews`, and `view_dashboard`.
- Does not access the database or session runtime state.

### Filter Lookup

- Reads `app/Config/Filters.php`, `app/Config/Routes.php`, and `app/Filters/` as text.
- Searches filter aliases, filter classes, route filters, global filters, CSRF references, role expressions, permission expressions, and before/after logic.
- Helps verify route-level expressions such as `role:admin,secretary` and `perm:manage_orders`.
- Does not execute filters or mutate request behavior.

## Docker Validation Status

| Area | Status | Notes |
|------|--------|------|
| Docker baseline | PASS | `32_oracle_mcp_docker_placeholder_run_validation.md` documents successful placeholder Docker run. |
| Minimal runtime Docker run | PASS | `37_mcp_runtime_docker_run_validation.md` documents successful rebuild and placeholder runtime run after the first runtime structure. |
| Current implemented tools in local Python | PASS | Runtime reports now show all six tools as implemented; latest tool implementation reports include local Python validation. |
| Current implemented tools in Docker | Needs Review | Docker was not run after the latest tool additions in this task. A controlled Docker rebuild/run validation should be performed separately. |
| Docker safety boundaries | PASS with ongoing caution | Runtime Compose keeps the repository read-only, output as the only writable mount, no ports, no `.env`, no secrets, no privileged mode. Docker seccomp warning remains accepted for local development only. |

## Safety Guarantees

- Runtime tools are read-only.
- Runtime tools use relative source paths.
- Runtime tools do not modify `app/`.
- Runtime tools do not modify controllers, models, services, filters, routes, migrations, seeders, views, or config files.
- Runtime tools do not execute PHP.
- Runtime tools do not bootstrap CodeIgniter.
- Runtime tools do not access the database.
- Runtime tools do not read `.env`.
- Runtime tools do not print or expose secrets.
- Runtime tools enforce result limits.
- Docker Compose keeps the repository mount read-only.
- Docker Compose exposes no ports.
- Docker Compose uses no `env_file`.
- Docker Compose defines no secrets.
- Docker Compose does not use privileged mode.
- The only writable runtime path is `ai/oracle/runtime/output/`.

## Known Limitations

- The runtime is still a placeholder-style local runtime, not a full MCP protocol server.
- Tools use conservative text parsing only.
- Text parsing cannot prove runtime behavior.
- Dynamic route definitions, dynamic model properties, traits, inherited behavior, callbacks, and service-level behavior may require manual review.
- Database state is not inspected.
- Session state is not inspected.
- Seeder evidence confirms source text only, not current database contents.
- Migration/model field mismatch still requires a deeper schema diff or manual schema audit.
- Docker validation should be rerun after the latest tool additions before treating the Docker runtime as fully current.
- Oracle MCP does not create tasks, plans, commits, or Domain KB updates automatically yet.

## Orchestration Flow Usage

Oracle MCP fits into the larger repository governance flow as a read-only repository guide and evidence collector:

```text
Domain KB
-> GitNexus
-> Oracle MCP
-> Task
-> Plan
-> Test
-> Commit
-> Domain KB Update
```

Expected role by stage:

| Stage | Oracle MCP Role |
|------|-----------------|
| Domain KB | Reads Domain KB and uses it as the source of truth for domain context. |
| GitNexus | Supports task metadata review by identifying likely affected domains and paths. |
| Oracle MCP | Provides read-only repo lookup, route lookup, model lookup, controller lookup, permission lookup, and filter lookup evidence. |
| Task | Helps draft or review task scope, affected paths, and risk notes. |
| Plan | Helps identify which files and domains need review before implementation. |
| Test | Helps define evidence-based validation targets without modifying code. |
| Commit | Suggests commit candidate notes and confirms whether KB impact exists. |
| Domain KB Update | Identifies candidate KB files that should be updated after code changes or tool expansion. |

## Suggested Commit Message

```text
docs(oracle): complete read-only MCP runtime capability expansion
```

If using GitNexus task IDs later, prefix the commit message with the relevant task id.

## Domain KB Update Candidate Notes

Later Domain KB updates should consider:

- `ai/domain-kb/01_domain_index.md`: mention that Oracle MCP now has read-only repository, route, controller, model, permission, and filter lookup capabilities.
- `ai/domain-kb/10_schema_model_matrix.md`: use `model_lookup` outputs as candidate evidence for future model/schema mapping improvements.
- `ai/domain-kb/02_route_permission_matrix.md`: use `route_lookup`, `controller_lookup`, `permission_lookup`, and `filter_lookup` as candidate evidence helpers.
- `ai/domain-kb/03_security_filter_audit.md`: use `filter_lookup` and `permission_lookup` to support future security/filter review.
- `ai/domain-kb/kb-manifest.yaml`: consider tracking Oracle runtime tool files if Oracle docs/runtime changes become part of KB drift governance.
- Future update reports under `ai/domain-kb/updates/`: document when Oracle MCP evidence is used to support a KB update.

No Domain KB changes are made in this report.

## Recommended Next Milestone

Recommended next milestone:

```text
GitNexus MCP integration review
```

Reason:

- Oracle MCP now has enough read-only repository inspection capability to support task/path/domain evidence.
- GitNexus is the next coordination layer that should decide how task metadata, changed paths, validation reports, commit candidates, and Domain KB update candidates are linked.
- Domain KB update skill discovery/review can follow after GitNexus integration boundaries are clear.

## Final Decision

Oracle MCP Runtime Expansion Complete: YES
