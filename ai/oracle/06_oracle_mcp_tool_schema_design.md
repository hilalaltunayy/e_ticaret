# 06 Oracle MCP Tool Schema Design

## Purpose

Define the future Oracle MCP tool schemas before implementation.

## Tool Design Principles

- Tools must be read-only by default.
- Tools must cite source KB files or repo paths.
- Tools must not invent repository facts.
- Tools must return `Needs Review` when evidence is unclear.
- Tools must not expose secrets.
- Tools must not modify app code.

## Tool List

1. `repo_lookup`
2. `domain_lookup`
3. `route_lookup`
4. `permission_lookup`
5. `kb_impact_check`
6. `task_draft_create`
7. `plan_draft_create`
8. `validation_check`
9. `kb_update_required_check`
10. `safety_boundary_check`

## Tool: repo_lookup

### Purpose

Locate repository files, classes, controllers, models, services, views, config files, or feature-related paths.

### Inputs

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `query` | string | Yes | File name, class name, feature hint, or path fragment. | `ProductsModel` |
| `path_scope` | array | No | Optional path prefixes to limit lookup. | `["app/Models", "app/Controllers"]` |
| `include_kb_context` | boolean | No | Whether to include relevant KB references. | `true` |

### Outputs

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `matches` | array | Matching repo paths and brief reason. | `["app/Models/ProductsModel.php"]` |
| `related_kb_files` | array | Relevant KB files when available. | `["ai/domain-kb/10_schema_model_matrix.md"]` |
| `summary` | string | Short evidence-based result. | `ProductsModel maps to Product / Catalog.` |

### Access Level

- `read_only_repo`
- `kb_read`
- `no_app_write`

### Failure Conditions

- Query is empty.
- Requested path is outside allowed repository boundary.
- Match requires reading real `.env` or sensitive runtime data.
- Evidence is ambiguous and cannot be resolved from repo or KB.

### Notes

- Must cite repo paths for all matches.
- Must return `Needs Review` for ambiguous symbols or multiple plausible owners.

## Tool: domain_lookup

### Purpose

Find domain ownership, related files, route patterns, permissions, and KB evidence for a feature or domain name.

### Inputs

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `domain_or_feature` | string | Yes | Domain name or feature hint. | `Product / Catalog` |
| `include_manifest_matches` | boolean | No | Whether to include manifest watched paths. | `true` |
| `include_claims` | boolean | No | Whether to include related claim IDs. | `true` |

### Outputs

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `domain` | string | Matched domain name. | `Product / Catalog` |
| `related_files` | array | Domain-related repo paths or path patterns. | `["app/Models/ProductsModel.php"]` |
| `related_kb_files` | array | KB files that describe the domain. | `["ai/domain-kb/01_domain_index.md"]` |
| `claims` | array | Related claim IDs when available. | `["MODEL-CLAIM-002"]` |
| `summary` | string | Short domain ownership summary. | `Product model ownership is catalog-first.` |

### Access Level

- `kb_read`
- `no_app_write`

### Failure Conditions

- Domain cannot be found in Domain KB or manifest.
- Domain name conflicts with multiple possible domains.
- Requested detail requires unsupported runtime inspection.

### Notes

- Domain KB remains the source of truth.
- Must return `Needs Review` if a domain is only inferred from broad or review-required manifest mapping.

## Tool: route_lookup

### Purpose

Find route definitions, route baseline evidence, controller bindings, filters, and route-related KB references.

### Inputs

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `route_or_uri` | string | No | Route URI, URI fragment, or route pattern. | `/admin/dashboard` |
| `controller` | string | No | Controller name or method. | `Admin\\DashboardController@index` |
| `domain` | string | No | Optional domain filter. | `Admin Panel` |
| `include_security_context` | boolean | No | Whether to include filter and permission notes. | `true` |

### Outputs

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `routes` | array | Matching route rows or route evidence. | `["/admin/dashboard"]` |
| `controllers` | array | Related controller methods. | `["Admin\\DashboardController@index"]` |
| `filters` | array | Related filter or guard evidence. | `["role:admin,secretary|perm:manage_dashboard"]` |
| `related_kb_files` | array | Route KB files used. | `["ai/domain-kb/06_route_baseline.md"]` |

### Access Level

- `read_only_repo`
- `kb_read`
- `no_app_write`

### Failure Conditions

- Neither route, controller, nor domain input is provided.
- Route evidence conflicts between KB and repo.
- Route exists only in grouped or wildcard baseline rows.

### Notes

- Must cite `app/Config/Routes.php` or route KB files.
- Must return `Needs Review` for grouped route rows until automated extraction exists.

## Tool: permission_lookup

### Purpose

Find permission, role, filter, RBAC, and secretary/admin access evidence.

### Inputs

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `permission` | string | No | Permission name. | `manage_orders` |
| `role` | string | No | Role name. | `secretary` |
| `filter` | string | No | Filter alias or class. | `RoleFilter` |
| `route_or_domain` | string | No | Route or domain context. | `Order` |

### Outputs

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `permissions` | array | Matching permissions. | `["manage_orders"]` |
| `roles` | array | Matching roles. | `["admin", "secretary"]` |
| `filters` | array | Related filter aliases/classes. | `["role", "permission"]` |
| `risks` | array | RBAC risks or unclear areas. | `["Permission enforcement is split."]` |
| `related_kb_files` | array | Security/RBAC KB sources. | `["ai/domain-kb/03_security_filter_audit.md"]` |

### Access Level

- `read_only_repo`
- `kb_read`
- `no_app_write`

### Failure Conditions

- No permission, role, filter, route, or domain input is provided.
- RBAC evidence is split or conflicting.
- Requested evidence requires live database inspection.

### Notes

- Must cite `app/Config/Filters.php`, route/security KB, or related model/seed evidence.
- Must return `Needs Review` for policy conflicts or controller-internal checks.

## Tool: kb_impact_check

### Purpose

Map changed paths to affected domains, impact levels, and KB files using `kb-manifest.yaml`.

### Inputs

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `changed_paths` | array | Yes | Repository paths changed or proposed for change. | `["app/Config/Routes.php"]` |
| `task_type` | string | No | GitNexus task type. | `route` |
| `risk_level` | string | No | Current task risk level. | `high` |

### Outputs

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `affected_domains` | array | Domains impacted by changed paths. | `["Auth", "Admin Panel"]` |
| `impact_levels` | object | Domain impact grouped by level. | `{"high_impact": ["auth"]}` |
| `affected_kb_files` | array | KB files that may need review/update. | `["ai/domain-kb/02_route_permission_matrix.md"]` |
| `review_required` | array | Paths/domains that require manual review. | `["order"]` |

### Access Level

- `kb_read`
- `no_app_write`

### Failure Conditions

- `changed_paths` is empty.
- Path does not match manifest and cannot be safely classified.
- Path matches `broad_review` or `needs_review` without manual review.

### Notes

- Manual review is required for `broad_review` or `needs_review` manifest matches.
- Must cite `ai/domain-kb/kb-manifest.yaml`.

## Tool: task_draft_create

### Purpose

Draft GitNexus-compatible task metadata from a user request, affected domains, changed paths, or risk notes.

### Inputs

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `user_request` | string | Yes | User or system task request. | `Add stock warning to product admin.` |
| `affected_domains` | array | No | Known affected domains. | `["Product / Catalog"]` |
| `affected_paths` | array | No | Known or expected changed paths. | `["app/Models/ProductsModel.php"]` |
| `risk_level` | string | No | Initial risk level. | `medium` |

### Outputs

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `task_draft` | object | Draft task metadata. | `{"task_type": "feature"}` |
| `missing_fields` | array | Required metadata still missing. | `["task_id"]` |
| `kb_update_required` | boolean | Whether KB update appears required. | `true` |
| `validation_needed` | boolean | Whether validation appears required. | `false` |

### Access Level

- `task_draft_write`
- `kb_read`
- `no_app_write`

### Failure Conditions

- User request is empty.
- Draft would require unsupported assumptions.
- Requested task implies app code changes but lacks affected domain or path evidence.

### Notes

- This tool drafts metadata only.
- It must not create a real GitNexus task unless future implementation explicitly supports that boundary.

## Tool: plan_draft_create

### Purpose

Draft a plan from task metadata, affected domains, KB impact, and risk notes.

### Inputs

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `task_draft` | object | Yes | Draft or existing task metadata. | `{"task_id": "GNX-1234"}` |
| `affected_domains` | array | No | Domains to consider. | `["Auth"]` |
| `affected_kb_files` | array | No | KB files likely impacted. | `["ai/domain-kb/03_security_filter_audit.md"]` |
| `risk_notes` | array | No | Known risks. | `["Security-sensitive change."]` |

### Outputs

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `plan_draft` | object | Draft plan steps and validation gates. | `{"steps": ["Review Filters.php"]}` |
| `expected_changes` | array | Expected code or documentation paths. | `["app/Config/Filters.php"]` |
| `kb_impact` | object | Expected KB files and domains. | `{"domains": ["Auth"]}` |
| `review_gates` | array | Required review/validation gates. | `["security validation"]` |

### Access Level

- `oracle_report_write`
- `task_draft_write`
- `kb_read`
- `no_app_write`

### Failure Conditions

- Task draft is missing.
- Plan would require app writes in Oracle planning mode.
- Risk level is high or critical but validation gate is missing.

### Notes

- This tool writes only draft plan/report artifacts in approved Oracle output locations.
- It does not perform implementation.

## Tool: validation_check

### Purpose

Validate task metadata, changed paths, KB impact, safety boundaries, or generated plans before implementation or commit.

### Inputs

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `task_metadata` | object | Yes | Task metadata to validate. | `{"task_id": "GNX-1234"}` |
| `changed_paths` | array | No | Changed paths to validate. | `["app/Config/Routes.php"]` |
| `kb_update_report` | string | No | KB update report path. | `ai/domain-kb/updates/report.md` |
| `validation_scope` | string | No | Scope of validation. | `route` |

### Outputs

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `findings` | array | Validation findings. | `["KB update report missing."]` |
| `decision` | string | Validation decision. | `needs_review` |
| `required_actions` | array | Actions required before proceeding. | `["Create KB update report."]` |
| `report_path` | string | Future report output path when written. | `ai/oracle/outputs/validation.md` |

### Access Level

- `kb_read`
- `oracle_report_write`
- `no_app_write`

### Failure Conditions

- Task metadata missing `task_id`.
- Required KB update report missing.
- High-risk task lacks validation or reviewer status.
- Validation would require app writes.

### Notes

- This tool can draft validation reports only in approved Oracle output directories.
- It must not mark ambiguous evidence as passed.

## Tool: kb_update_required_check

### Purpose

Determine whether a task or changed path set requires a KB update.

### Inputs

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `changed_paths` | array | Yes | Changed or proposed paths. | `["app/Models/ProductsModel.php"]` |
| `task_type` | string | No | GitNexus task type. | `schema` |
| `risk_level` | string | No | Risk level. | `medium` |
| `change_summary` | string | No | Human summary of the change. | `Updated allowed fields.` |

### Outputs

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `kb_update_required` | boolean | Whether KB update is required. | `true` |
| `affected_kb_files` | array | KB files requiring review/update. | `["ai/domain-kb/10_schema_model_matrix.md"]` |
| `reason` | string | Evidence-based reason. | `Model path matches Product / Catalog high impact.` |
| `skip_allowed` | boolean | Whether skipping may be allowed with reason. | `false` |

### Access Level

- `kb_read`
- `no_app_write`

### Failure Conditions

- `changed_paths` missing.
- Manifest cannot map the path.
- Change summary conflicts with manifest impact.
- Review-required paths are treated as automatically safe.

### Notes

- Must cite manifest mappings.
- Must return `needs_review` for unmatched or ambiguous paths.

## Tool: safety_boundary_check

### Purpose

Check whether a requested Oracle action respects runtime mode, filesystem access rules, secret boundaries, and tool access limits.

### Inputs

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `requested_tool` | string | Yes | Tool requested for execution. | `repo_lookup` |
| `oracle_mode` | string | Yes | Current Oracle mode. | `guide_mode` |
| `target_paths` | array | No | Paths the tool wants to read or write. | `["app/Config/Routes.php"]` |
| `write_intent` | boolean | Yes | Whether the action intends to write. | `false` |
| `secret_access_intent` | boolean | No | Whether the action intends to read secrets. | `false` |

### Outputs

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `allowed` | boolean | Whether the action is allowed. | `true` |
| `denial_reason` | string | Reason if blocked. | `app/ write is not allowed.` |
| `required_mode` | string | Required mode if current mode is insufficient. | `planning_mode` |
| `boundary_notes` | array | Policy notes used for the decision. | `["guide_mode is read-only"]` |

### Access Level

- `no_app_write`
- `kb_read`

### Failure Conditions

- Tool requests app write.
- Tool requests secret access without explicit approval.
- Tool requests output outside allowed directories.
- Tool mode does not allow the requested action.

### Notes

- This tool should run before any future tool execution with write intent.
- It should deny by default when boundary evidence is unclear.

## Required Output Fields for Every Tool

Every tool output must include:

- `status`
- `confidence`
- `sources`
- `needs_review`
- `warnings`

## Confidence Values

- `high`: Evidence is directly supported by specific KB files or repository paths.
- `medium`: Evidence is supported but indirect, grouped, or partially inferred from KB structure.
- `low`: Evidence is weak, ambiguous, broad, or requires manual review.

## Status Values

- `success`: Tool completed and evidence is sufficient.
- `partial`: Tool completed but some evidence or mapping is incomplete.
- `needs_review`: Tool found ambiguity, broad/review-required evidence, or insufficient support.
- `blocked`: Tool was denied by safety, mode, secret, or filesystem boundary.
- `error`: Tool failed due to invalid input, unavailable source, or unexpected runtime failure.

## Safety Requirements

- No secret output.
- No app write.
- No unsupported claims.
- Source evidence required.
- Manual review required for `broad_review` or `needs_review` manifest matches.
- All write-capable future tools must pass `safety_boundary_check`.
- Tool outputs must preserve source paths so decisions can be audited.

## Final Summary

This schema is ready for validation.

It defines future Oracle MCP tool contracts, required outputs, status and confidence values, access levels, and safety behavior without implementing Docker, MCP code, scripts, automation, secrets, or application changes.
