# 41 route_lookup Tool Plan

## Purpose

Plan the second real read-only Oracle MCP tool: `route_lookup`.

`route_lookup` should inspect route definition files in the repository and return matching route metadata, especially CodeIgniter 4 route definitions such as `app/Config/Routes.php`.

This is a planning document only. It does not implement tool logic, modify runtime files, modify `app/`, read secrets, or change Docker/Compose behavior.

## Tool Contract

Tool name:

```text
route_lookup
```

Tool purpose:

- Search route definitions by route path, controller name, or keyword.
- Return route metadata that can be safely derived from route definition files.
- Help map URI patterns to controller targets without modifying the application.

Tool access level:

```text
read_only_route_files
```

Allowed behavior:

- Read approved route definition files only.
- Search route definitions for a user-provided query.
- Return matched route path or URI pattern.
- Return HTTP method when detectable.
- Return controller target when detectable.
- Return source file path.
- Return limited results.
- Return safe `no_match` responses.

Prohibited behavior:

- Do not write, delete, rename, move, format, or modify files.
- Do not execute route files as PHP.
- Do not call the application runtime.
- Do not access the database.
- Do not read `.env`.
- Do not read secrets.
- Do not infer unsupported route behavior without marking it as `needs_review`.

## Input Format

Proposed input fields:

| Field | Type | Required | Description | Example |
|------|------|----------|-------------|---------|
| `query` | string | Yes | Route path, controller name, route name, or keyword | `admin` |
| `search_mode` | string | No | `auto`, `route_path`, `controller`, or `keyword` | `auto` |
| `max_results` | integer | No | Maximum number of route matches | `20` |
| `case_sensitive` | boolean | No | Whether matching should be case-sensitive | `false` |
| `include_uncertain` | boolean | No | Whether to include partially parsed matches | `true` |

Input rules:

- `query` must not be empty.
- `max_results` should default to `20`.
- `max_results` should have a hard upper limit such as `100`.
- `search_mode` should default to `auto`.
- Query values must be treated as text only, not executable code.

Supported search examples:

- Route path: `/admin`, `login`, `dashboard`
- Controller name: `Login`, `OrderController`, `ProductController`
- Keyword: `admin`, `order`, `login`, `dashboard`

## Output Format

Proposed output fields:

| Field | Type | Description | Example |
|------|------|-------------|---------|
| `status` | string | `success`, `no_match`, `partial`, `needs_review`, or `error` | `success` |
| `query` | string | Original safe query value | `admin` |
| `matches` | array | Matched route metadata | See match fields below |
| `result_count` | integer | Number of returned matches | `5` |
| `truncated` | boolean | Whether results were limited by `max_results` | `false` |
| `warnings` | array | Non-fatal parsing warnings | `[]` |
| `sources` | array | Route definition files inspected | `["app/Config/Routes.php"]` |

Proposed match item fields:

| Field | Type | Description | Example |
|------|------|-------------|---------|
| `route_path` | string | Route URI or group pattern when detectable | `admin/orders` |
| `http_method` | string | HTTP method when detectable | `GET` |
| `controller_target` | string | Controller and method target when detectable | `OrderController::index` |
| `route_name` | string/null | Named route if detectable | `admin.orders` |
| `source_file` | string | Route source file | `app/Config/Routes.php` |
| `confidence` | string | `high`, `medium`, or `low` | `medium` |
| `notes` | array | Parsing or uncertainty notes | `["Route group context inferred"]` |

Safe no-match response:

```json
{
  "status": "no_match",
  "query": "missing-route",
  "matches": [],
  "result_count": 0,
  "truncated": false,
  "warnings": [],
  "sources": ["app/Config/Routes.php"]
}
```

## Safety Boundaries

`route_lookup` must:

- Be read-only.
- Read route definition files only.
- Never modify route files.
- Never execute PHP code.
- Never bootstrap CodeIgniter.
- Never query the database.
- Never read `.env`.
- Never read secrets, credentials, wallets, keys, tokens, or certificates.
- Never expose ports.
- Never mutate `app/`.
- Return `needs_review` when route syntax cannot be parsed confidently.

Approved initial source files:

```text
app/Config/Routes.php
```

Future route source expansion requires separate review.

## CI4 Route Parsing Notes

The first implementation should use conservative text parsing, not PHP execution.

Common CodeIgniter 4 route patterns to detect:

```php
$routes->get('path', 'Controller::method');
$routes->post('path', 'Controller::method');
$routes->put('path', 'Controller::method');
$routes->delete('path', 'Controller::method');
$routes->match(['get', 'post'], 'path', 'Controller::method');
$routes->group('prefix', static function ($routes) {
    $routes->get('child', 'Controller::method');
});
```

Parsing rules:

- Direct `$routes->get/post/put/delete/patch/options()` calls can usually produce high-confidence matches.
- `$routes->match()` can produce medium/high confidence if method list, path, and target are simple literals.
- `$routes->group()` requires prefix context; nested or dynamic groups may require `needs_review`.
- Dynamic variables, concatenated strings, imported route files, closures, or complex callbacks should be marked `needs_review`.
- If HTTP method is not detectable, return `UNKNOWN`.
- If controller target is not detectable, return `null` and include a warning.
- Do not infer filter or permission behavior from route files alone unless the syntax explicitly includes it.

## Validation Checklist

Before implementation, validate:

| Check | Expected Result |
|------|-----------------|
| Tool is read-only | No write, delete, rename, move, format, or modify calls |
| Route source limited | Initial implementation reads only `app/Config/Routes.php` |
| No PHP execution | Route file is parsed as text only |
| No DB access | No database import, query, or connection logic |
| No secrets | No `.env`, wallet, key, token, credential, or certificate access |
| No ports | No network listener or exposed port |
| Query modes | Supports route path, controller name, and keyword search |
| Result shape | Returns route path, HTTP method, controller target, and source file |
| Limited results | Enforces default and hard max result limits |
| No-match safe | Empty result returns `no_match` without error |
| Uncertain parsing | Complex syntax returns `needs_review` or low confidence |
| Registry integration | Tool registration remains read-only |

## Risks And Mitigations

| Risk | Mitigation |
|------|------------|
| Regex parser misses complex CI4 syntax | Mark uncertain cases as `needs_review` |
| Group prefixes parsed incorrectly | Track simple group context only and lower confidence for nested/dynamic groups |
| Route filters mistaken for permissions | Do not infer RBAC unless explicitly parsed from route syntax |
| Dynamic PHP route definitions | Return `needs_review` instead of guessing |
| Too many matches | Enforce `max_results` |
| Source file expansion risk | Keep first implementation limited to `app/Config/Routes.php` |

## Final Decision

Ready for `route_lookup` implementation? YES
