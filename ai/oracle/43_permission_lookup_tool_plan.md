# 43 permission_lookup Tool Plan

## Purpose

Plan a read-only `permission_lookup` tool for the Oracle MCP runtime.

The tool should help inspect RBAC access rules without modifying the application. It should support analysis of which permissions, roles, and filters protect admin, secretary, order, review, dashboard, and page routes.

This is a planning document only. It does not implement tool logic, modify runtime files, modify `app/`, run Docker, access the database, or read `.env` or secrets.

## Initial Read-Only Sources

The first implementation may inspect these files as read-only text sources:

- `app/Config/Routes.php`
- `app/Config/Filters.php`
- `app/Database/Seeds/InitialAuthSeeder.php`
- `app/Models/PermissionModel.php`
- `app/Models/RolePermissionModel.php`
- `app/Models/UserPermissionModel.php`
- `app/Services/AuthService.php`

No database connection is allowed. Seeder/model/service files must be parsed as text only.

## Tool Contract

Tool name:

```text
permission_lookup
```

Tool purpose:

- Search for RBAC-related evidence by route path, permission code, role name, or module keyword.
- Return route/filter/permission evidence from approved source files.
- Help identify whether access appears role-based, permission-based, campaign-specific, or unclear.

Tool access level:

```text
read_only_rbac_sources
```

Allowed behavior:

- Read approved source files as text.
- Search route groups and filters in `Routes.php`.
- Search filter alias/configuration references in `Filters.php`.
- Search permission seed definitions in `InitialAuthSeeder.php`.
- Search model/service references related to permissions and auth.
- Return limited, source-anchored evidence.

Prohibited behavior:

- Do not write, delete, rename, move, format, or modify files.
- Do not execute PHP.
- Do not bootstrap CodeIgniter.
- Do not query the database.
- Do not read `.env`.
- Do not read secrets, wallets, keys, tokens, certificates, or credentials.
- Do not infer permissions as confirmed unless source evidence exists.
- Do not modify routes, filters, models, services, seeders, or controllers.

## Supported Search Inputs

| Input Type | Description | Example |
|-----------|-------------|---------|
| Route path | Admin/user route path or partial path | `admin/dashboard` |
| Permission code | Permission or capability code | `manage_orders` |
| Role name | Role keyword or literal role name | `secretary` |
| Module keyword | Domain/module keyword | `dashboard`, `order`, `review`, `page` |

## Input Format

Proposed input fields:

| Field | Type | Required | Description | Example |
|------|------|----------|-------------|---------|
| `query` | string | Yes | Route path, permission code, role name, or module keyword | `manage_orders` |
| `search_mode` | string | No | `auto`, `route_path`, `permission`, `role`, or `keyword` | `auto` |
| `max_results` | integer | No | Maximum number of evidence matches | `20` |
| `include_context` | boolean | No | Whether to include short route/filter context snippets | `true` |

Input rules:

- `query` must not be empty.
- `max_results` should default to `20`.
- `max_results` should have a hard upper limit such as `100`.
- Query text must not be executed.
- Absolute paths must not be accepted as source overrides.

## Output Format

Proposed output fields:

| Field | Type | Description | Example |
|------|------|-------------|---------|
| `status` | string | `success`, `no_results`, `partial`, `needs_review`, or `error` | `success` |
| `query` | string | Original safe query | `manage_orders` |
| `matches` | array | Source-anchored RBAC evidence | See match fields below |
| `result_count` | integer | Number of returned matches | `4` |
| `truncated` | boolean | Whether results were limited | `false` |
| `warnings` | array | Non-fatal uncertainty notes | `[]` |
| `sources` | array | Files inspected | `["app/Config/Routes.php"]` |

Proposed match fields:

| Field | Type | Description | Example |
|------|------|-------------|---------|
| `source_file` | string | Evidence source path | `app/Config/Routes.php` |
| `line_number` | integer/null | Line number when available | `203` |
| `match_type` | string | `route`, `filter`, `permission_seed`, `model_reference`, `service_reference`, or `unknown` | `route` |
| `route_path` | string/null | Route path if detected | `admin/orders` |
| `filter` | string/null | Filter expression if detected | `role:admin,secretary|perm:manage_orders` |
| `permission_code` | string/null | Permission code if detected | `manage_orders` |
| `role_name` | string/null | Role name if detected | `secretary` |
| `matched_text` | string | Safe matched line or normalized text | `$routes->group('admin', ['filter' => ...` |
| `confidence` | string | `high`, `medium`, or `low` | `medium` |
| `notes` | array | Parsing notes or uncertainty | `["Permission inferred from route filter text"]` |

Safe no-result response:

```json
{
  "status": "no_results",
  "query": "missing_permission",
  "matches": [],
  "result_count": 0,
  "truncated": false,
  "warnings": [],
  "sources": []
}
```

## Safety Boundaries

`permission_lookup` must:

- Be read-only.
- Read only approved source files.
- Parse files as text only.
- Never execute PHP.
- Never bootstrap CodeIgniter.
- Never query or write the database.
- Never read `.env`.
- Never read secrets.
- Never modify `app/`.
- Never modify routes, filters, models, services, seeders, or controllers.
- Never expose ports.
- Return `needs_review` when source evidence is ambiguous.

## Result Limits

- Default `max_results`: `20`.
- Hard maximum `max_results`: `100`.
- Stop collecting once the hard limit is reached.
- Return `truncated: true` when results are limited.
- Prefer source diversity when possible: route evidence, filter evidence, seed evidence, and model/service references.

## Parsing Strategy

Initial implementation should use conservative text parsing:

- Parse route groups in `app/Config/Routes.php`.
- Detect filter strings such as:
  - `role:admin`
  - `role:admin,secretary|perm:manage_orders`
  - `campaign_access`
- Associate simple route group filters with child route paths.
- Parse route lines for path and controller target when possible.
- Parse `app/Config/Filters.php` for filter aliases and filter class mappings.
- Parse `InitialAuthSeeder.php` for permission codes and role-permission references.
- Parse permission models/services for table names, allowed fields, and permission lookup behavior.

Confidence rules:

- `high`: Direct route filter or explicit permission code in source line.
- `medium`: Permission appears in nearby group context or seed/model/service reference.
- `low`: Keyword appears but relationship to RBAC is unclear.
- `needs_review`: Dynamic syntax, multiline expressions, or ambiguous filter composition.

## Known Limitations

- Text parsing may miss complex PHP expressions.
- Nested route groups may require lower confidence.
- Runtime filter behavior may differ from static route text.
- Database state is not checked.
- User-specific permissions are not resolved from live data.
- Actual access decisions may depend on filter implementation details not fully visible from configuration alone.
- Review routes may be absent or unclear; the tool should report no results or needs review rather than inventing access rules.

## Test Examples

Required example searches:

| Query | Expected Focus |
|------|----------------|
| `admin/dashboard` | Route group protecting admin dashboard, expected dashboard permission/filter evidence |
| `admin/orders` | Order management route group and `manage_orders` filter evidence |
| `secretary` | Routes/filters where secretary is included |
| `manage_orders` | Permission filter references for order management |
| `manage_reviews` | Permission seed/filter evidence if present, otherwise safe no-results or needs-review |
| `view_dashboard` | Permission seed/filter evidence if present, otherwise safe no-results or needs-review |

Additional useful examples:

- `campaign_access`
- `manage_dashboard`
- `manage_products`
- `role:admin`
- `perm:manage_shipping`

## Validation Checklist

Before implementation, validate:

| Check | Expected Result |
|------|-----------------|
| Read-only behavior | No writes, deletes, renames, moves, formats, or modifications |
| Source scope | Reads only approved source files |
| No PHP execution | Parses text only |
| No DB access | Does not connect to or query database |
| No secrets | Does not read `.env` or secret-like files |
| Search modes | Supports route path, permission code, role name, and module keyword |
| Output evidence | Includes source file, line number, match type, and matched text |
| Permission evidence | Extracts permission codes from route filters and seed/model/service text |
| Role evidence | Extracts role names from route filters and source text |
| Result limits | Enforces default and hard maximum result limits |
| No-results safe | Returns `no_results` without error |
| Uncertainty safe | Returns `needs_review` or low confidence for ambiguous evidence |

## Final Decision

Ready for `permission_lookup` implementation? YES
