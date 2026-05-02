# 33 MCP Runtime Structure Plan

## Purpose

Plan the minimal MCP runtime structure after successful Docker placeholder validation.

This is a planning document only. It does not create runtime source files, implement an MCP server, create secrets, add ports, change mounts, or modify application code.

## Source Context

- Docker baseline completed in `ai/oracle/32_oracle_mcp_docker_placeholder_run_validation.md`.
- Current runtime contains only the approved Docker baseline files.
- Placeholder runtime is executed with:

```text
docker compose run --rm oracle-mcp-runtime
```

## 1. Proposed Folder Structure Under `ai/oracle/runtime/`

Future implementation may add the following structure only after explicit approval:

```text
ai/oracle/runtime/
  Dockerfile
  compose.yaml
  README.md
  output/
    .gitkeep
  src/
    server.*
    config.*
    tools/
      registry.*
      repo_file_lookup.*
      route_lookup.*
      model_lookup.*
      controller_lookup.*
      permission_lookup.*
    safety/
      boundary_check.*
    utils/
      source_paths.*
      response_format.*
```

Planning notes:

- `src/` is not created yet.
- No tool implementation exists yet.
- No secrets, credentials, wallets, or `.env` files belong in this structure.
- `output/` remains the only writable runtime path.
- `app/` remains read-only through the existing repository mount.

## 2. Minimal MCP Server Entrypoint Design

The future minimal MCP server entrypoint should:

- Start in read-only guide mode by default.
- Load local configuration from non-secret defaults.
- Resolve the repository root from the mounted `/workspace` path.
- Register only approved read-only tools.
- Return structured responses with status, confidence, sources, warnings, and needs-review flags.
- Refuse any request that attempts to modify `app/`, read secrets, write outside `output/`, or access a database for mutation.
- Exit cleanly when used in one-shot validation mode.

The entrypoint must not:

- Start web servers.
- Expose ports.
- Connect to Oracle.
- Connect to the application database.
- Modify repository files.
- Read `.env`.
- Load real credentials.

## 3. Tool Registry Concept

The future tool registry should be a small internal map of allowed tools.

Each tool registration should define:

| Field | Purpose |
|------|---------|
| `name` | Stable MCP tool name |
| `description` | Human-readable tool purpose |
| `access_level` | Read-only or report-output-only boundary |
| `allowed_paths` | Paths the tool may inspect |
| `blocked_paths` | Paths the tool must never read or write |
| `input_schema` | Structured input definition |
| `output_schema` | Structured output definition |
| `handler` | Tool handler function |

Registry rules:

- Only explicitly registered tools can run.
- Tools default to read-only.
- Unknown tools must return `blocked`.
- Tools must cite source files.
- Tools must return `Needs Review` when evidence is unclear.

## 4. First Safe Read-Only Tools

### `repo_file_lookup`

Purpose:

- Locate and summarize repository files by path or safe path pattern.

Allowed access:

- Read-only repository access.
- No `.env`, secrets, vendor binaries, or broad binary scans.

Expected sources:

- `/workspace/app/**`
- `/workspace/ai/domain-kb/**`
- `/workspace/ai/oracle/**`

### `route_lookup`

Purpose:

- Inspect route definitions and summarize matching route entries.

Allowed access:

- Read-only access to route configuration and route-related KB files.

Expected sources:

- `/workspace/app/Config/Routes.php`
- `/workspace/ai/domain-kb/02_route_permission_matrix.md`
- `/workspace/ai/domain-kb/06_route_baseline.md`

### `model_lookup`

Purpose:

- Inspect model files and map them to known schema/model KB facts.

Allowed access:

- Read-only access to model files and schema/model KB files.

Expected sources:

- `/workspace/app/Models/**`
- `/workspace/app/Database/Migrations/**`
- `/workspace/ai/domain-kb/10_schema_model_matrix.md`

### `controller_lookup`

Purpose:

- Inspect controller files and summarize controller responsibilities and likely domain ownership.

Allowed access:

- Read-only access to controller files and related KB files.

Expected sources:

- `/workspace/app/Controllers/**`
- `/workspace/ai/domain-kb/01_domain_index.md`
- `/workspace/ai/domain-kb/02_route_permission_matrix.md`

### `permission_lookup`

Purpose:

- Inspect filters, permission mappings, and RBAC-related KB facts.

Allowed access:

- Read-only access to filters, security KB files, and permission-related documentation.

Expected sources:

- `/workspace/app/Config/Filters.php`
- `/workspace/ai/domain-kb/02_route_permission_matrix.md`
- `/workspace/ai/domain-kb/03_security_filter_audit.md`
- `/workspace/ai/domain-kb/09_claim_id_registry.md`

## 5. Boundaries

Hard boundaries for the future MCP runtime:

- Repository access is read-only by default.
- `app/` must never be mutated.
- No database writes.
- No migration execution.
- No route/controller/model/view/service/filter edits.
- No `.env` reading.
- No secret output.
- No Oracle credentials or wallet files.
- No ports.
- No host networking.
- No privileged containers.
- No writable mounts except the existing `ai/oracle/runtime/output/` rule.

Any request outside these boundaries must return `blocked` with a clear reason.

## 6. Docker Usage

The runtime should continue to use the controlled local command pattern:

```text
docker compose run --rm oracle-mcp-runtime
```

Rules:

- Keep `docker compose up` disallowed until a persistent MCP server lifecycle is separately designed and validated.
- Keep ports disabled.
- Keep the repository mount read-only.
- Keep only `output/` writable.
- Do not mount `.env`.
- Do not add `env_file` or Compose secrets.

## 7. Validation Checklist Before Implementation

Before creating `src/` or MCP code, validate:

| Check | Expected Result |
|------|-----------------|
| Docker baseline | `32_oracle_mcp_docker_placeholder_run_validation.md` is `PASS` |
| Runtime boundaries | Current Dockerfile and Compose boundaries remain unchanged |
| No ports | Compose still has no `ports` section |
| No secrets | Compose still has no `.env`, `env_file`, or secrets |
| Read-only repo | Repository mount remains `:ro` |
| Output writable only | Only `output/` is writable |
| Tool scope | First tools are read-only only |
| Source citation | Tool outputs require source paths |
| App protection | `app/` mutation remains blocked |
| Implementation approval | User explicitly approves MCP runtime implementation |

## 8. Risks And Mitigations

| Risk | Mitigation |
|------|------------|
| Tool reads too broadly | Restrict allowed paths per tool and block secrets |
| Tool invents repository facts | Require source citations and confidence values |
| App mutation attempt | Enforce read-only mount and tool boundary checks |
| Secret leakage | Block `.env`, wallet, key, token, credential, and certificate reads |
| Accidental persistent container | Continue using `docker compose run --rm` |
| Port exposure | Keep Compose port-free until separately approved |
| Database mutation | Do not implement database write tools |
| Overgrown first implementation | Start with read-only lookup tools only |

## 9. Final Decision

Ready for MCP runtime implementation? YES

This means ready for the next explicit implementation-planning or controlled implementation task only. It does not authorize adding secrets, ports, Oracle connectivity, database writes, persistent services, or application mutations.
