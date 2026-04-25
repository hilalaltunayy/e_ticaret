# 05 Oracle Docker MCP Runtime Validation

## Purpose

Validate whether the Oracle Docker/MCP runtime design is safe, complete, and ready for tool schema design.

## Validation Checklist

| Check | Expected | Actual | Status | Notes |
|------|----------|--------|--------|------|
| Docker is design-only, not implemented. | Runtime design should not create Docker files or commands. | `04_oracle_docker_mcp_runtime_design.md` describes Docker runtime concepts and explicitly excludes Dockerfile implementation. | Pass | No Docker implementation is created. |
| MCP server is design-only, not implemented. | Runtime design should not create MCP server code. | MCP tool set is described as future design; MCP server implementation is out of scope. | Pass | No MCP code is created. |
| Repository mount is read-only by default. | Repository should be mounted read-only first. | Runtime model uses `/workspace/repo:ro` and states default runtime should start with read-only repository access. | Pass | Correct safe default. |
| App code is never writable. | `app/` must not be writable. | Runtime architecture and security design state Oracle must not modify application code and app code is never writable. | Pass | Strong app boundary. |
| Oracle output write path is limited. | Writes should be limited to Oracle outputs. | Optional writable output mount is `/workspace/repo/ai/oracle/outputs:rw`; output writes are limited to `ai/oracle/outputs`. | Pass | Clear write boundary. |
| Secret variables are marked as secret. | Secret variables must be identified. | `ORACLE_API_KEY` is marked secret. | Pass | Good baseline for future `.env.example`. |
| Real secrets are not stored in repo. | Real values must not be committed. | Runtime design says no real secrets may be stored in the repository and real values must not be committed. | Pass | Consistent with boundary plan. |
| Beginner Docker explanation exists. | Design should explain Docker concepts without commands. | Local Docker usage section explains image, container, volume mount, read-only mount, and environment variable. | Pass | No executable commands included. |
| MCP tools are listed with access boundaries. | Future tools should define input, output, access, and write behavior. | MCP Tool Set table includes input, output, access, and writes columns. | Pass | Ready for schema design. |
| Oracle modes are defined. | Runtime modes should define read/write/prohibited actions. | `guide_mode`, `planning_mode`, `validation_mode`, and `kb_assist_mode` are defined. | Pass | Clear mode separation. |
| Tool behavior rules require source evidence. | Tools should cite repository or KB evidence. | Tool behavior rules require source files or KB files. | Pass | Prevents unsupported guidance. |
| Tool behavior rules prevent invented repo facts. | Tools must not hallucinate repository facts. | Tool behavior rules say tools must not invent repository facts. | Pass | Strong evidence rule. |
| Tool behavior rules require secret redaction. | Secrets must be redacted. | Tool behavior rules and security design require secret redaction. | Pass | Correct security behavior. |
| Out-of-scope section excludes implementation. | Implementation should be excluded. | Dockerfile, MCP server, real AI provider integration, secret creation, GitNexus, Orchestrator, and app code changes are out of scope. | Pass | Correct planning-only scope. |
| First implementation path is incremental and safe. | Implementation path should start small and safe. | Path starts with `.env.example` later, Dockerfile later, MCP server later, then read-only repo lookup first. | Pass | Safe staged implementation path. |

## Security Review

- Any risk of app write access?
  - Status: Low
  - The design states repository access is read-only by default, app code is never writable, and tools must not modify `app/`.
- Any risk of secret leakage?
  - Status: Low / Needs Future Enforcement
  - Secret rules are clear, but future implementation must enforce redaction before real secrets are introduced.
- Any unclear writable mount?
  - Status: Low
  - Writable mount is limited to `/workspace/repo/ai/oracle/outputs:rw`.
- Any missing boundary between guide/planning/kb_assist modes?
  - Status: Low
  - Modes define allowed reads, writes, outputs, and prohibited actions.

## Missing or Weak Areas

- MCP tool schemas are not defined yet.
- Request/response JSON shapes are not defined yet.
- No enforcement design exists yet for `ORACLE_ALLOWED_WRITE_DIRS`.
- No redaction test cases are defined yet.
- No Docker mount validation checklist exists yet.
- No tool-level error format exists yet.
- No audit log format exists yet.

These are non-blocking gaps for moving to tool schema design.

## Final Verdict

PASS: Ready for Oracle MCP tool schema design.
