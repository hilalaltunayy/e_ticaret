# 46 filter_lookup Tool Plan

## Purpose

Plan a read-only `filter_lookup` tool for the Oracle MCP runtime.

The tool should inspect CodeIgniter 4 filter definitions and custom filter behavior before controller review. It should help verify route filter expressions such as `role:admin,secretary` and `perm:manage_orders`, and identify whether `auth`, `role`, `perm`, `csrf`, and secure headers are globally or route-level enforced.

This is a planning document only. It does not implement tool logic, modify runtime files, modify `app/`, run Docker, access the database, or read `.env` or secrets.

## Initial Read-Only Sources

The first implementation may inspect these sources as read-only text:

- `app/Config/Filters.php`
- `app/Filters/`
- `app/Config/Routes.php`

No PHP execution is allowed. Filter classes must be parsed as text only.

## Tool Contract

Tool name:

```text
filter_lookup
```

Tool purpose:

- Search filter aliases, global filters, route-level filters, and custom filter class behavior.
- Return source-anchored evidence for filter definitions and enforcement clues.
- Help decide whether filter-level review is complete before controller review.

Tool access level:

```text
read_only_filter_sources
```

Allowed behavior:

- Read approved filter and route source files as text.
- Search filter aliases in `app/Config/Filters.php`.
- Search global `before` and `after` filter arrays.
- Search route-level filter expressions in `app/Config/Routes.php`.
- Search custom filter classes under `app/Filters/`.
- Return limited, source-anchored evidence.

Prohibited behavior:

- Do not write, delete, rename, move, format, or modify files.
- Do not execute PHP.
- Do not bootstrap CodeIgniter.
- Do not query or write the database.
- Do not read `.env`.
- Do not read secrets, wallets, keys, tokens, certificates, or credentials.
- Do not modify `Filters.php`, routes, or filter classes.
- Do not infer runtime behavior as confirmed unless source evidence supports it.

## Supported Search Inputs

| Input Type | Description | Example |
|-----------|-------------|---------|
| Filter alias | CI4 filter alias name | `auth`, `role`, `perm`, `csrf` |
| Filter class name | Custom filter class name | `RoleFilter`, `PermissionFilter`, `AuthFilter` |
| Route path | Route path or partial route path | `admin/dashboard`, `admin/orders` |
| Permission/role expression | Route filter expression or part of it | `role:admin,secretary`, `perm:manage_orders` |
| Keyword | Filter/config keyword | `before`, `after`, `globals`, `aliases`, `filters`, `except` |

## Input Format

Proposed input fields:

| Field | Type | Required | Description | Example |
|------|------|----------|-------------|---------|
| `query` | string | Yes | Alias, class name, route path, filter expression, or keyword | `perm:manage_orders` |
| `search_mode` | string | No | `auto`, `alias`, `class`, `route`, `expression`, or `keyword` | `auto` |
| `max_results` | integer | No | Maximum evidence rows | `20` |
| `include_context` | boolean | No | Whether to include short nearby context lines | `true` |

Input rules:

- `query` must not be empty.
- `max_results` should default to `20`.
- `max_results` should have a hard maximum such as `100`.
- Query text must be treated as text only.
- Absolute source overrides must not be accepted.

## Output Format

Proposed output fields:

| Field | Type | Description | Example |
|------|------|-------------|---------|
| `status` | string | `success`, `no_results`, `partial`, `needs_review`, or `error` | `success` |
| `query` | string | Original safe query | `perm:manage_orders` |
| `matches` | array | Source-anchored filter evidence | See match fields below |
| `result_count` | integer | Number of returned matches | `4` |
| `truncated` | boolean | Whether results were limited | `false` |
| `warnings` | array | Non-fatal uncertainty notes | `[]` |
| `sources` | array | Files inspected | `["app/Config/Filters.php"]` |

Proposed match fields:

| Field | Type | Description | Example |
|------|------|-------------|---------|
| `source_file` | string | Evidence source path | `app/Config/Routes.php` |
| `line_number` | integer/null | Line number when available | `202` |
| `matched_text` | string | Safe matched line or normalized text | `$routes->group('admin', ['filter' => ...` |
| `detected_type` | string | `alias`, `global_filter`, `route_filter`, `filter_class`, `before_logic`, or `after_logic` | `route_filter` |
| `related_route` | string/null | Route path if detectable | `admin/orders` |
| `related_alias` | string/null | Filter alias if detectable | `perm` |
| `related_role` | string/null | Role if detectable | `admin,secretary` |
| `related_permission` | string/null | Permission code if detectable | `manage_orders` |
| `risk_hint` | string/null | Static risk hint if detectable | `No explicit auth filter in route group` |
| `confidence` | string | `high`, `medium`, or `low` | `medium` |

Safe no-result response:

```json
{
  "status": "no_results",
  "query": "missing_filter",
  "matches": [],
  "result_count": 0,
  "truncated": false,
  "warnings": [],
  "sources": []
}
```

## Safety Boundaries

`filter_lookup` must:

- Be read-only.
- Read only approved filter and route sources.
- Use relative source paths only.
- Never execute PHP.
- Never bootstrap CodeIgniter.
- Never query or write the database.
- Never read `.env`.
- Never read secret-like files.
- Never modify `app/`.
- Never modify `Filters.php`, routes, filter classes, controllers, models, services, seeders, or views.
- Enforce result limits.
- Return `needs_review` when source evidence is ambiguous.

## Result Limits

- Default `max_results`: `20`.
- Hard maximum `max_results`: `100`.
- Stop collecting once the hard limit is reached.
- Return `truncated: true` when results are limited.

## Parsing Strategy

Initial implementation should use conservative text parsing:

- Parse `app/Config/Filters.php` aliases for filter alias to class mapping.
- Parse `app/Config/Filters.php` globals for `before` and `after` enforcement.
- Parse `app/Config/Routes.php` route groups and route-level filter expressions.
- Parse files under `app/Filters/` for class names and before/after method logic.
- Detect simple role and permission expressions:
  - `role:admin`
  - `role:admin,secretary`
  - `perm:manage_orders`
  - `role:admin,secretary|perm:manage_orders`
- Detect risk hints such as:
  - filter alias exists but global enforcement is disabled
  - route group uses `role` or `perm` without explicit `auth`
  - CSRF appears commented out globally
  - secure headers appear commented out globally

Confidence rules:

- `high`: Direct alias/global/route/filter-class line match.
- `medium`: Nearby context supports the relationship.
- `low`: Keyword exists but exact enforcement meaning is unclear.
- `needs_review`: Dynamic syntax, multiline conditions, or unclear filter control flow.

## Known Limitations

- Text parsing cannot fully prove runtime access behavior.
- Filter class internals may require manual review even after lookup results.
- Combined syntax such as `role:admin,secretary|perm:manage_orders` may have semantics defined inside custom filters.
- Global filters commented out in config should be reported as evidence, not automatically treated as a vulnerability without context.
- Controller-level access checks are out of scope for this tool.

## Test Examples

Required example searches:

| Query | Expected Focus |
|------|----------------|
| `auth` | Auth alias and any route/global auth references |
| `csrf` | CSRF alias and global before status |
| `role:admin,secretary` | Route groups using combined admin/secretary role filter |
| `perm:manage_orders` | Admin order route permission filter |
| `RoleFilter` | Role filter class alias and class file |
| `PermissionFilter` | Permission filter class alias and class file |
| `admin/dashboard` | Dashboard route filter evidence |
| `admin/orders` | Order route filter evidence |

Additional useful examples:

- `before`
- `after`
- `globals`
- `secureheaders`
- `campaign_access`
- `except`

## Validation Checklist

Before implementation, validate:

| Check | Expected Result |
|------|-----------------|
| Read-only behavior | No writes, deletes, renames, moves, formats, or modifications |
| Source scope | Reads only `app/Config/Filters.php`, `app/Filters/`, and `app/Config/Routes.php` |
| No PHP execution | Parses text only |
| No DB access | Does not connect to or query database |
| No secrets | Does not read `.env` or secret-like files |
| Search inputs | Supports alias, class, route path, filter expression, and keyword |
| Output evidence | Includes source file, line number, matched text, detected type, and related fields |
| Risk hints | Provides cautious risk hints when detectable |
| Result limits | Enforces default and hard maximum result limits |
| No-results safe | Returns `no_results` without error |
| Uncertainty safe | Returns `needs_review` or low confidence for ambiguous evidence |

## Final Decision

Ready for `filter_lookup` implementation? YES
