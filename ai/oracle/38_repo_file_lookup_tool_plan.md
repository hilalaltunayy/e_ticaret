# 38 repo_file_lookup Tool Plan

## Purpose

Plan the first real read-only Oracle MCP tool: `repo_file_lookup`.

This is a planning document only. It does not implement tool logic, modify runtime source files, modify `app/`, read secrets, or change Docker/Compose behavior.

## Tool Contract

Tool name:

```text
repo_file_lookup
```

Tool purpose:

- Search for files inside the mounted repository by filename or partial path.
- Return safe file path metadata only.
- Do not read file contents yet.

Tool access level:

```text
read_only_repo
```

Allowed behavior:

- Scan the mounted repository root.
- Match files by filename or partial relative path.
- Return matched relative paths.
- Return file extension/type metadata.
- Return a limited number of results.
- Return a safe no-match response.

Prohibited behavior:

- Do not write files.
- Do not delete files.
- Do not rename files.
- Do not modify files.
- Do not read file contents.
- Do not expose `.env` files.
- Do not expose secrets.
- Do not scan unsafe or heavy folders.

## Input Format

Proposed input fields:

| Field | Type | Required | Description | Example |
|------|------|----------|-------------|---------|
| `query` | string | Yes | Filename or partial relative path to search for | `ProductsModel` |
| `max_results` | integer | No | Maximum number of matches to return | `20` |
| `include_extensions` | array of strings | No | Optional extension allowlist | `[".php", ".md"]` |
| `case_sensitive` | boolean | No | Whether matching should be case-sensitive | `false` |

Input rules:

- `query` must not be empty.
- `max_results` should default to a conservative value such as `20`.
- `max_results` should have a hard upper limit such as `100`.
- Path traversal input such as `..` must be normalized and kept inside the repository boundary.
- Absolute host paths must not be accepted as scan roots.

## Output Format

Proposed output fields:

| Field | Type | Description | Example |
|------|------|-------------|---------|
| `status` | string | `success`, `no_match`, `needs_review`, or `error` | `success` |
| `query` | string | Original safe query value | `ProductsModel` |
| `matches` | array | Matched file metadata | See below |
| `result_count` | integer | Number of returned matches | `3` |
| `truncated` | boolean | Whether results were limited by `max_results` | `false` |
| `ignored_folders` | array | Folders skipped during scan | `["vendor", "node_modules"]` |
| `warnings` | array | Non-fatal warnings | `[]` |
| `sources` | array | Source roots or KB references used | `["/workspace"]` |

Proposed match item fields:

| Field | Type | Description | Example |
|------|------|-------------|---------|
| `relative_path` | string | Repository-relative path | `app/Models/ProductsModel.php` |
| `extension` | string | File extension | `.php` |
| `file_type` | string | Coarse file type label | `php` |

No-match behavior:

```json
{
  "status": "no_match",
  "query": "unknown-file",
  "matches": [],
  "result_count": 0,
  "truncated": false,
  "ignored_folders": [],
  "warnings": [],
  "sources": ["/workspace"]
}
```

## Safety Boundaries

`repo_file_lookup` must:

- Be read-only.
- Scan only the mounted repository.
- Never write, delete, rename, move, format, or modify files.
- Never read file contents.
- Never return `.env` paths.
- Never return known secret-like file paths.
- Never scan outside `/workspace`.
- Never accept a user-provided scan root outside the mounted repository.
- Never follow symlinks outside the mounted repository.
- Return `needs_review` if repository boundary validation is unclear.

## Ignored Folders

The tool must ignore these folders:

```text
.git
vendor
node_modules
writable
public/uploads
ai/oracle/output
```

Additional recommended ignored paths:

```text
ai/oracle/runtime/output
```

Secret-like path filters:

- `.env`
- `.env.*`
- `*.key`
- `*.pem`
- `*.p12`
- `*.pfx`
- `*wallet*`
- `*credential*`
- `*secret*`
- `*token*`

The tool should not return these paths even if the query matches.

## Validation Checklist

Before implementation, validate:

| Check | Expected Result |
|------|-----------------|
| Tool is read-only | No write, delete, rename, move, or modify calls |
| Repository boundary | Scan root is fixed to mounted repository |
| No content reading | Tool returns path metadata only |
| Ignored folders enforced | Unsafe/heavy folders are skipped |
| Secret paths blocked | `.env` and secret-like paths are not returned |
| Result limit enforced | `max_results` has default and hard maximum |
| No-match safe | Empty result returns `no_match` without error |
| Symlink safety | Symlinks do not escape repository boundary |
| Output schema stable | Output includes status, matches, count, warnings, and sources |
| Registry integration | Tool registration remains read-only |

## Risks And Mitigations

| Risk | Mitigation |
|------|------------|
| Heavy scan slows runtime | Ignore heavy folders and enforce result limits |
| Secret-like paths exposed | Block `.env` and secret-like filenames |
| Path traversal | Normalize paths and keep all scans under `/workspace` |
| Symlink escape | Do not follow symlinks outside repository boundary |
| Over-broad output | Return path metadata only, never contents |
| Unsupported assumptions | Return `needs_review` when boundary checks are unclear |

## Final Decision

Ready for `repo_file_lookup` implementation? YES
