# 08 Oracle MCP Implementation Plan

## Purpose

Define the safe implementation plan for the first local Docker-based Oracle MCP.

## Implementation Scope

- First implementation is local only.
- Docker-based runtime is planned.
- MCP server will be minimal.
- `app/` remains read-only for Oracle.
- No production deployment.
- No CI integration yet.
- No GitNexus implementation yet.
- No Orchestrator implementation yet.
- This document is an implementation plan only; it does not create runtime files, Docker files, MCP server code, scripts, automation, or secrets.

## Proposed File Structure

Future files to create later, not in this planning step:

```text
ai/oracle/runtime/
  Dockerfile
  docker-compose.yml
  .env.example
  README.md
  src/
    server.*
    config.*
    tools/
      repo_lookup.*
      domain_lookup.*
      kb_impact_check.*
      safety_boundary_check.*
```

## First Tool Implementation Order

| Order | Tool | Why First/Late | Required Inputs | Required Sources | Expected Output | Failure Mode |
|-------|------|----------------|-----------------|------------------|-----------------|--------------|
| 1 | `safety_boundary_check` | Must exist first so every future tool can verify mode, path, write, and secret boundaries. | `requested_tool`, `oracle_mode`, `target_paths`, `write_intent`, `secret_access_intent` | `ai/oracle/02_oracle_runtime_boundary_plan.md`, `ai/oracle/06_oracle_mcp_tool_schema_design.md` | Allow/deny decision with reason and boundary notes. | Block if app write, secret access, wrong mode, or disallowed output path is requested. |
| 2 | `repo_lookup` | First useful read-only repository capability; validates read-only mount and source citation behavior. | `query`, optional `path_scope`, optional `include_kb_context` | Repository files, optional Domain KB references | Matching repo paths, related KB files, source-backed summary. | Return `needs_review` if query is ambiguous or path scope is invalid. |
| 3 | `domain_lookup` | Adds KB-aware guidance after repo lookup works. | `domain_or_feature`, optional manifest/claim flags | `ai/domain-kb/01_domain_index.md`, `ai/domain-kb/kb-manifest.yaml`, claim registry when needed | Domain summary, related files, KB sources, claims. | Return `needs_review` for ambiguous or review-required domain evidence. |
| 4 | `kb_impact_check` | Core bridge between changed paths and KB update decisions. | `changed_paths`, optional `task_type`, optional `risk_level` | `ai/domain-kb/kb-manifest.yaml` | Affected domains, impact levels, affected KB files, review-required items. | Block or return `needs_review` for empty paths, unmatched paths, `broad_review`, or `needs_review` matches. |
| 5 | `route_lookup` | Depends on repo lookup and KB lookup foundations. | Route URI, controller, domain, optional security context | `app/Config/Routes.php`, `ai/domain-kb/02_route_permission_matrix.md`, `ai/domain-kb/06_route_baseline.md` | Route evidence, controllers, filters, related KB files. | Return `needs_review` for grouped rows, wildcard baseline rows, or repo/KB conflicts. |
| 6 | `permission_lookup` | Requires route/security evidence and should follow route lookup. | Permission, role, filter, route, or domain | `app/Config/Filters.php`, security KB, route matrix, permission-related KB files | Permissions, roles, filters, risks, related KB files. | Return `needs_review` for split RBAC paths, controller-internal checks, or missing live DB evidence. |
| 7 | `task_draft_create` | Should wait until lookup and impact checks can provide reliable domain/path evidence. | `user_request`, optional domains, paths, risk level | Domain KB, GitNexus contract docs, manifest impact results | Draft task metadata, missing fields, KB update decision, validation decision. | Block if request is empty or would require unsupported assumptions. |
| 8 | `plan_draft_create` | Depends on task draft quality and impact evidence. | `task_draft`, domains, affected KB files, risk notes | Task draft, Domain KB, GitNexus workflow policy | Draft plan steps, expected changes, KB impact, review gates. | Block if high/critical risk lacks validation gates or plan implies app writes by Oracle. |
| 9 | `validation_check` | Comes after drafts and impact checks so it can validate complete task/plan metadata. | `task_metadata`, changed paths, KB update report, validation scope | Oracle docs, Domain KB, GitNexus policy docs | Findings, decision, required actions, optional report path. | Block if task ID, KB report, validation report, or approval is missing when required. |
| 10 | `kb_update_required_check` | Final advisory helper once impact, task, and validation semantics are stable. | `changed_paths`, task type, risk level, change summary | `kb-manifest.yaml`, KB update policy, GitNexus policy | Required/not required decision, affected KB files, reason, skip allowed flag. | Return `needs_review` for unmatched, ambiguous, or review-required paths. |

## Docker Implementation Plan

- Use local Docker container.
- Mount repository read-only first.
- Allow writable output only under `ai/oracle/outputs` later.
- Use environment variables for config.
- Use `.env.example` placeholders only.
- Real `.env` must not be committed.
- Start in `guide_mode`.
- Add write-capable report output only after `safety_boundary_check` is validated.

## Beginner Setup Plan

Conceptual setup steps for a later guide:

1. Verify Docker Desktop is installed.
2. Create runtime folder.
3. Create `.env.example`.
4. Create local `.env` from example.
5. Build Docker image.
6. Run container.
7. Connect MCP client.
8. Test `safety_boundary_check`.
9. Test `repo_lookup`.
10. Test `domain_lookup`.

Exact commands are intentionally excluded here; commands should be added only in a later setup guide.

## Secret Handling Plan

- No API keys in repo.
- `.env` ignored.
- `.env.example` placeholders only.
- Logs must redact secrets.
- Tool outputs must redact secrets.
- `ORACLE_API_KEY` must be treated as secret.
- Real secrets should be passed through local `.env`, environment variables, or Docker secrets only.

## Minimal MCP Test Plan

1. Safety boundary test:
   - Confirm app write attempts are blocked.
   - Confirm real `.env` reads are blocked by default.
2. Repo lookup test:
   - Query a known file such as `ProductsModel.php`.
   - Verify sources are cited.
3. Domain lookup test:
   - Query `Product / Catalog`.
   - Verify Domain KB files are cited.
4. KB impact test:
   - Use changed path `app/Config/Routes.php`.
   - Verify impact levels and review-required domains are returned.
5. Route lookup test:
   - Query an existing route from the route baseline.
   - Verify grouped or wildcard rows return `needs_review` when exact evidence is insufficient.
6. Permission lookup test:
   - Query a known permission such as `manage_orders`.
   - Verify RBAC sources and risks are cited.

## Validation Before Use

Require:

- Docker starts.
- MCP client connects.
- Tools return structured output.
- Sources are cited.
- `app/` write attempt blocked.
- Secrets not printed.
- Read-only repo mount confirmed.
- `safety_boundary_check` runs before any write-capable tool.
- Tool output includes required fields: `status`, `confidence`, `sources`, `needs_review`, and `warnings`.

## Risks

- Docker path mount confusion.
- Secret leakage.
- Wrong writable mount.
- Hallucinated repo facts.
- MCP client misconfiguration.
- Windows path issues.
- Read-only mount not correctly enforced.
- Output directory accidentally broadened beyond `ai/oracle/outputs`.
- Tool schemas drifting from `06_oracle_mcp_tool_schema_design.md`.

## Out of Scope

- Production deployment
- CI/CD
- GitNexus automation
- Orchestrator
- App code modification
- Automatic commits
- Dockerfile creation in this step
- MCP server code creation in this step
- Secret creation

## Final Summary

This implementation plan is ready for validation.

The plan starts with the safety boundary, then adds read-only repository and KB-aware lookup, then task/plan drafting, validation, and KB update decision support. It preserves the planning-only boundary and does not create Docker files, MCP server code, scripts, automation, secrets, runtime folders, or application code changes.
