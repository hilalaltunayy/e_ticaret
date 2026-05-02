# 54 controller_lookup Tool Plan

## Purpose

Plan a read-only `controller_lookup` tool for the Oracle MCP runtime.

The tool should inspect controller files safely without modifying application code. It should help trace a route handler to its controller class and method, and speed up review of admin, secretary, user, order, product, dashboard, and page builder flows.

This is a planning document only. It does not implement tool logic, modify runtime files, modify `app/`, run Docker, access the database, or read `.env` or secrets.

## Initial Read-Only Sources

The first implementation may inspect these sources as read-only text:

- `app/Controllers/`
- `app/Config/Routes.php`

No PHP execution is allowed. Controller files must be parsed as text only.

## Tool Contract

Tool name:

```text
controller_lookup
```

Tool purpose:

- Search controller classes, methods, route handlers, redirects, service calls, view returns, and permission checks.
- Map route paths to controller targets using `app/Config/Routes.php`.
- Return source-anchored controller evidence for faster review.

Tool access level:

```text
read_only_controller_sources
```

Allowed behavior:

- Read approved controller and route source files as text.
- Search controller class names.
- Search method names.
- Search route handler strings.
- Search keywords in controller files.
- Return limited, source-anchored evidence.

Prohibited behavior:

- Do not write, delete, rename, move, format, or modify files.
- Do not execute PHP.
- Do not bootstrap CodeIgniter.
- Do not call controller methods.
- Do not query or write the database.
- Do not read `.env`.
- Do not read secrets, wallets, keys, tokens, certificates, or credentials.
- Do not modify controllers, routes, services, models, views, filters, seeders, or migrations.

## Supported Search Inputs

| Input Type | Description | Example |
|-----------|-------------|---------|
| Controller class name | Fully or partially qualified controller class | `Admin\\DashboardController`, `Admin\\Orders`, `Login` |
| Method name | Controller method name | `index`, `store`, `update`, `datatables`, `auth` |
| Route path | Route path or partial route path | `admin/dashboard`, `admin/orders`, `login` |
| Keyword | Domain or behavior keyword | `dashboard`, `order`, `product`, `page`, `auth`, `secretary`, `permission` |

## Input Format

Proposed input fields:

| Field | Type | Required | Description | Example |
|------|------|----------|-------------|---------|
| `query` | string | Yes | Controller class, method, route path, or keyword | `Admin\\Orders` |
| `search_mode` | string | No | `auto`, `controller`, `method`, `route`, or `keyword` | `auto` |
| `max_results` | integer | No | Maximum evidence rows | `20` |
| `include_route_resolution` | boolean | No | Whether route path should resolve to controller target first | `true` |

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
| `query` | string | Original safe query | `admin/orders` |
| `matches` | array | Source-anchored controller evidence | See match fields below |
| `result_count` | integer | Number of returned matches | `5` |
| `truncated` | boolean | Whether results were limited | `false` |
| `warnings` | array | Non-fatal uncertainty notes | `[]` |
| `sources` | array | Files inspected | `["app/Controllers/Admin/Orders.php"]` |

Proposed match fields:

| Field | Type | Description | Example |
|------|------|-------------|---------|
| `source_file` | string | Evidence source path | `app/Controllers/Admin/Orders.php` |
| `line_number` | integer/null | Line number when available | `41` |
| `matched_text` | string | Safe matched line or normalized text | `public function index()` |
| `detected_type` | string | `controller_class`, `method`, `route_handler`, `redirect`, `service_call`, `view_return`, or `permission_check` | `method` |
| `controller_class` | string/null | Controller class when detectable | `Admin\\Orders` |
| `method` | string/null | Method when detectable | `index` |
| `related_route` | string/null | Route path if detected/resolved | `admin/orders` |
| `related_service` | string/null | Related service if detectable | `DashboardService` |
| `related_model` | string/null | Related model if detectable | `UserPermissionModel` |
| `risk_hint` | string/null | Static risk hint if detectable | `Controller reads session user directly` |
| `confidence` | string | `high`, `medium`, or `low` | `medium` |

Safe no-result response:

```json
{
  "status": "no_results",
  "query": "missing_controller",
  "matches": [],
  "result_count": 0,
  "truncated": false,
  "warnings": [],
  "sources": []
}
```

## Safety Boundaries

`controller_lookup` must:

- Be read-only.
- Read only approved controller and route sources.
- Use relative source paths only.
- Never execute PHP.
- Never instantiate controllers.
- Never bootstrap CodeIgniter.
- Never query or write the database.
- Never read `.env`.
- Never read secret-like files.
- Never modify `app/`.
- Never modify Docker files or runtime configuration.
- Enforce result limits.
- Return `needs_review` when source evidence is ambiguous.

## Ignored Folders

The implementation should ignore unsafe or irrelevant folders:

```text
.git
vendor
node_modules
writable
public/uploads
ai/oracle/output
ai/oracle/runtime/output
```

## Result Limits

- Default `max_results`: `20`.
- Hard maximum `max_results`: `100`.
- Stop collecting once the hard limit is reached.
- Return `truncated: true` when results are limited.

## Parsing Strategy

Initial implementation should use conservative text parsing:

- Parse `app/Config/Routes.php` for controller targets like `Admin\Orders::index`.
- Convert controller targets to likely controller file paths:
  - `Admin\Orders::index` -> `app/Controllers/Admin/Orders.php`
  - `Login::auth` -> `app/Controllers/Login.php`
- Parse controller files as text only.
- Detect class declarations:
  - `class DashboardController`
  - `class Orders`
- Detect public methods:
  - `public function index()`
  - `public function datatables()`
  - `public function auth()`
- Detect view returns:
  - `return view(...)`
- Detect redirects:
  - `return redirect()->to(...)`
  - `return redirect()->back()`
- Detect service/model references:
  - `new DashboardService()`
  - `use App\Services\...`
  - `use App\Models\...`
- Detect permission/session hints:
  - `session()->get('user')`
  - `session('role')`
  - `permission`
  - `role`
  - `canManage`

Confidence rules:

- `high`: Direct class/method/route handler match.
- `medium`: Route target resolves to a file and nearby method match.
- `low`: Keyword appears but behavior relationship is unclear.
- `needs_review`: Dynamic calls, traits, inherited methods, or indirect service behavior.

## Known Limitations

- Text parsing cannot prove runtime behavior.
- Trait methods may be missed unless trait files are separately included in a future plan.
- Dynamic method names and callbacks may require manual review.
- Service-level behavior is only referenced, not fully analyzed.
- Controller-level permission checks may be indirect or absent by design.

## Test Examples

Required example searches:

| Query | Expected Focus |
|------|----------------|
| `admin/dashboard` | Resolve route to `Admin\\DashboardController::index` and inspect controller |
| `Admin\\DashboardController` | Find dashboard controller class and methods |
| `Admin\\Orders` | Find order controller class and methods |
| `Login` | Find login controller class and auth method |
| `auth` | Find login/auth route, AuthService use, and auth method |
| `datatables` | Find datatables methods in admin controllers |
| `permission` | Find permission-related controller references |
| `redirect` | Find redirect behavior in controllers |

Additional useful examples:

- `store`
- `update`
- `page`
- `product`
- `secretary`
- `dashboard`

## Validation Checklist

Before implementation, validate:

| Check | Expected Result |
|------|-----------------|
| Read-only behavior | No writes, deletes, renames, moves, formats, or modifications |
| Source scope | Reads only `app/Controllers/` and `app/Config/Routes.php` |
| No PHP execution | Parses text only |
| No DB access | Does not connect to or query database |
| No secrets | Does not read `.env` or secret-like files |
| Route resolution | Route paths can resolve to controller targets when possible |
| Output evidence | Includes source file, line number, matched text, detected type, and related fields |
| Result limits | Enforces default and hard maximum result limits |
| No-results safe | Returns `no_results` without error |
| Uncertainty safe | Returns `needs_review` or low confidence for ambiguous evidence |

## Final Decision

Ready for `controller_lookup` implementation? YES
