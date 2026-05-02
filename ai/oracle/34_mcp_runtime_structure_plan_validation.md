# 34 MCP Runtime Structure Plan Validation

## Purpose

Validate `ai/oracle/33_mcp_runtime_structure_plan.md` before any MCP runtime source files are implemented.

This validation is documentation-only. It does not create runtime source files, modify `app/`, modify `compose.yaml`, create secrets, or implement an MCP server.

## Validation Checklist

| Check | Expected | Actual | Status | Notes |
|------|----------|--------|--------|------|
| Runtime structure stays under `ai/oracle/runtime/` | Proposed files and folders must be inside runtime only | Proposed `src/`, `tools/`, `safety/`, and `utils/` are all under `ai/oracle/runtime/` | Pass | Source: `ai/oracle/33_mcp_runtime_structure_plan.md` |
| No runtime files created by plan | Plan must remain planning-only | 33 states `src/` is not created yet and no tool implementation exists | Pass | Implementation remains gated |
| Avoids `app/` changes | Plan must not authorize app mutation | 33 states `app/` must never be mutated and remains read-only | Pass | Source: Boundaries section |
| Avoids secrets | Plan must block secrets and `.env` access | 33 blocks `.env`, credentials, wallets, keys, tokens, certificates, and secret output | Pass | Source: Boundaries and risks sections |
| Avoids ports | Plan must not add or expose ports | 33 states no ports and Compose must remain port-free | Pass | Source: Boundaries and Docker Usage sections |
| Avoids privileged mode | Plan must not allow privileged containers | 33 states no privileged containers | Pass | Source: Boundaries section |
| Avoids database writes | Plan must not allow DB mutation | 33 states no database writes and no migration execution | Pass | Source: Boundaries section |
| First tools are read-only | All first tools must be read-only | `repo_file_lookup`, `route_lookup`, `model_lookup`, `controller_lookup`, and `permission_lookup` are read-only | Pass | Source: First Safe Read-Only Tools section |
| Docker usage remains limited | Must use only `docker compose run --rm oracle-mcp-runtime` | 33 keeps this as the controlled local command pattern | Pass | `docker compose up` remains disallowed |
| Writable mount rule preserved | Only `output/` may be writable | 33 keeps only `ai/oracle/runtime/output/` writable | Pass | Source: Boundaries and Docker Usage sections |
| Source citation required | Tool outputs must cite evidence | 33 requires source paths, confidence, warnings, and needs-review flags | Pass | Reduces unsupported claim risk |
| Implementation blocked until validation | Runtime implementation must not proceed without validation | 33 requires validation before creating `src/` or MCP code | Pass | This 34 document supplies that validation gate |

## Risks Found

- Broad read-only lookup tools could still read too much if implementation does not enforce per-tool allowed paths.
- `repo_file_lookup` could accidentally inspect sensitive files if blocked path rules are weak.
- Tool output could become speculative if source citation and confidence rules are not enforced.
- Future implementation could accidentally drift from the current Docker boundary if Compose is changed without review.

## Required Corrections

- No corrections are required in `33_mcp_runtime_structure_plan.md` before proceeding.

Implementation-time requirements:

- Enforce blocked paths for `.env`, secrets, wallets, keys, tokens, certificates, and credential files.
- Keep all first tools read-only.
- Keep source citations mandatory.
- Keep `docker compose run --rm oracle-mcp-runtime` as the only approved execution pattern.
- Do not add ports, privileged mode, database writes, or app mutation logic.

## Approved Next Action

- Proceed to a separate, explicit MCP runtime implementation task.
- That task may create only the approved minimal runtime source structure under `ai/oracle/runtime/`.
- The first implementation must remain read-only and must not introduce secrets, ports, database writes, app mutation, Oracle connectivity, or persistent services.

## Final Verdict

PASS
