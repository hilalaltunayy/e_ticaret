# 39 repo_file_lookup Tool Plan Validation

## Purpose

Validate `ai/oracle/38_repo_file_lookup_tool_plan.md` before implementing the first real read-only tool.

This validation is documentation-only. It does not implement tool logic, modify runtime files, modify `app/`, or modify Dockerfile/Compose files.

## Validation Checklist

| Check | Expected | Actual | Status | Notes |
|------|----------|--------|--------|------|
| Tool is read-only | Tool must not write, delete, rename, move, format, or modify files | Plan explicitly prohibits write, delete, rename, move, format, and modification behavior | Pass | Source: Safety Boundaries |
| Searches file paths only | Tool must not read file contents | Plan states it returns path metadata only and must never read file contents | Pass | Source: Tool Contract and Safety Boundaries |
| Scans only mounted repository | Tool must stay inside repository boundary | Plan fixes scan scope to the mounted repository and `/workspace` boundary | Pass | Source: Safety Boundaries |
| Ignores `.git` | Unsafe/heavy folder skipped | `.git` listed under ignored folders | Pass | Source: Ignored Folders |
| Ignores `vendor` | Unsafe/heavy folder skipped | `vendor` listed under ignored folders | Pass | Source: Ignored Folders |
| Ignores `node_modules` | Unsafe/heavy folder skipped | `node_modules` listed under ignored folders | Pass | Source: Ignored Folders |
| Ignores `writable` | Runtime-heavy folder skipped | `writable` listed under ignored folders | Pass | Source: Ignored Folders |
| Ignores `public/uploads` | Upload-heavy folder skipped | `public/uploads` listed under ignored folders | Pass | Source: Ignored Folders |
| Ignores `ai/oracle/output` | Oracle output folder skipped | `ai/oracle/output` listed under ignored folders | Pass | Source: Ignored Folders |
| Recommends runtime output ignore | Avoids scanning generated runtime output | `ai/oracle/runtime/output` listed as additional recommended ignored path | Pass | Source: Ignored Folders |
| Does not expose `.env` | `.env` paths must not be returned | Plan blocks `.env` and `.env.*` | Pass | Source: Secret-like path filters |
| Does not expose secret-like files | Keys, wallets, credentials, secrets, and tokens must not be returned | Plan blocks `*.key`, `*.pem`, `*.p12`, `*.pfx`, `*wallet*`, `*credential*`, `*secret*`, and `*token*` | Pass | Source: Secret-like path filters |
| Limited results only | Tool must enforce result limits | Plan defines default `max_results` and hard upper limit | Pass | Source: Input Format |
| Handles no-match safely | No-match must not error | Plan defines `status: no_match` with empty matches | Pass | Source: Output Format |
| No DB access required | Tool must not need database access | Plan only scans repository file paths and does not require DB access | Pass | Implied by contract and boundaries |
| No ports required | Tool must not expose or require ports | Plan is runtime tool logic only and does not require ports | Pass | No networking behavior defined |
| No secrets required | Tool must not require secrets | Plan blocks secrets and does not require credentials | Pass | Source: Safety Boundaries |
| No `app/` changes required | Tool must not modify application files | Plan is read-only and prohibits modification | Pass | Source: Safety Boundaries |
| Implementation blocked until validation | Implementation should wait for PASS | This validation provides the required gate | Pass | Current final verdict is PASS |

## Risks

- If implementation follows symlinks without boundary checks, it could inspect paths outside `/workspace`.
- If secret-like path filters are implemented as case-sensitive only, uppercase or mixed-case secret filenames could be returned.
- If result limits are not enforced before collecting all matches, very large repositories could still cause heavy scans.
- If ignored folders are matched only by exact string without normalization, path separator differences could bypass ignores.

## Required Corrections

- No corrections are required in `38_repo_file_lookup_tool_plan.md` before implementation.

Implementation-time requirements:

- Normalize paths before ignore checks.
- Treat secret-like filters case-insensitively.
- Enforce `max_results` during scanning, not only after collecting all results.
- Do not follow symlinks outside `/workspace`.
- Return path metadata only; do not read file contents.

## Approved Next Action

- Proceed to a separate, explicit implementation task for `repo_file_lookup`.
- Implementation must remain read-only and limited to `ai/oracle/runtime/`.
- Do not modify `app/`, Dockerfile, or `compose.yaml` during the tool implementation unless a separate explicit task approves it.

## Final Verdict

PASS
