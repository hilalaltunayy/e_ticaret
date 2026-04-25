# 09 Oracle MCP Implementation Plan Validation

## Purpose

Validate whether the Oracle MCP implementation plan is safe, complete, and ready for beginner setup guide design.

## Validation Checklist

| Check | Expected | Actual | Status | Notes |
|------|----------|--------|--------|------|
| Implementation plan is local-only. | First implementation should be local only. | `08_oracle_mcp_implementation_plan.md` states first implementation is local only. | Pass | Correct scope. |
| Docker is planned but not implemented. | Docker runtime may be planned, but no Dockerfile should be created. | Docker is planned; document states no Docker files are created. | Pass | `ai/oracle/runtime/` was not created by the plan. |
| MCP server is planned but not implemented. | MCP server may be planned, but no server code should be created. | MCP server is described as minimal future work; no server code is created. | Pass | Correct planning-only boundary. |
| `app/` remains read-only for Oracle. | Oracle must not modify application code. | Implementation scope states `app/` remains read-only for Oracle. | Pass | Consistent with runtime boundary. |
| Proposed file structure is clear. | Future runtime file layout should be described without creating it. | Proposed structure includes future Dockerfile, compose file, `.env.example`, README, server/config, and tool files. | Pass | Future-only and not created. |
| Tool implementation order is safe. | Safety and read-only lookup should come before write-capable tools. | Tool order starts with `safety_boundary_check`, then `repo_lookup`, `domain_lookup`, and `kb_impact_check`. | Pass | Safe sequence. |
| `safety_boundary_check` is first. | Boundary check should be first implemented tool. | Listed as order 1. | Pass | Correct. |
| `repo_lookup` and `domain_lookup` come before write-capable tools. | Read-only lookup should precede draft/report tools. | `repo_lookup` and `domain_lookup` are order 2 and 3; draft/report tools come later. | Pass | Correct staged rollout. |
| Secret handling plan is clear. | Secret rules should be explicit. | Plan states no API keys in repo, `.env` ignored, placeholders only, redaction required. | Pass | Good baseline. |
| `.env.example` only uses placeholders. | Example env file should not contain real secrets. | Plan states `.env.example` placeholders only. | Pass | Correct. |
| Real `.env` must not be committed. | Real local secrets must stay out of repository. | Plan states real `.env` must not be committed and `.env` ignored. | Pass | Correct. |
| Beginner setup plan exists. | Beginner steps should be listed conceptually without commands. | Plan includes beginner setup steps and explicitly excludes exact commands until a later guide. | Pass | Good next-step bridge. |
| Minimal MCP test plan exists. | Minimal tests should be defined before implementation. | Plan includes safety boundary, repo lookup, domain lookup, KB impact, route lookup, and permission lookup tests. | Pass | Sufficient initial coverage. |
| Validation before use exists. | Use should require runtime and safety checks. | Plan requires Docker starts, MCP client connects, structured output, source citation, app write block, and secret safety. | Pass | Strong gate before use. |
| Risks include Windows path issues. | Windows path risks should be explicit for local Docker. | Risks include Windows path issues. | Pass | Relevant to the current environment. |
| Out of scope excludes production, CI, GitNexus, Orchestrator, app code modification. | Implementation plan should defer these. | Out of Scope includes production, CI/CD, GitNexus automation, Orchestrator, app code modification, automatic commits, Dockerfile creation in this step, MCP server code creation in this step, and secret creation. | Pass | Complete exclusion list. |

## Security Review

- Any risk of app write access?
  - Status: Low
  - The plan keeps `app/` read-only, starts with `safety_boundary_check`, and blocks app write attempts.
- Any risk of secret leakage?
  - Status: Low / Needs Future Enforcement
  - The plan has clear secret handling rules, but future implementation must enforce log and output redaction.
- Any unclear implementation boundary?
  - Status: Low
  - The document states it is an implementation plan only and does not create runtime files, Docker files, MCP code, scripts, automation, or secrets.
- Any risky tool order?
  - Status: Low
  - The order is conservative: safety first, read-only lookup next, write-capable draft/report tools later.

## Missing or Weak Areas

- Exact beginner setup commands are not defined yet.
- Dockerfile content is not defined yet.
- MCP server framework/library choice is not defined yet.
- `.env.example` contents are not defined yet.
- Output directory creation policy is not defined yet.
- Redaction implementation details are not defined yet.
- Windows path examples are not defined yet.

These are non-blocking because the next step is beginner setup guide design, not runtime implementation.

## Final Verdict

PASS: Ready for Oracle beginner setup guide.
