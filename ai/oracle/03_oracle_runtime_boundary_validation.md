# 03 Oracle Runtime Boundary Validation

## Purpose

Validate whether the Oracle runtime boundary is safe, complete, and ready for Docker/MCP runtime design.

## Validation Checklist

| Check | Expected | Actual | Status | Notes |
|------|----------|--------|--------|------|
| `app/` is read-only. | Oracle must not write application code. | `02_oracle_runtime_boundary_plan.md` marks `app/` as read-only and states no app writes in guide, planning, or default MCP mode. | Pass | Strong application-code boundary. |
| `ai/domain-kb/` is read-only by default. | Domain KB should be read-only except explicitly approved KB update mode. | Boundary plan marks Domain KB as read-only by default and writable only for approved KB update skill execution. | Pass | Correct source-of-truth boundary. |
| `ai/oracle/` can be writable for reports/plans. | Oracle should write only its planning and validation docs by default. | Boundary plan marks `ai/oracle/` writable for Oracle reports and plans. | Pass | Correct planning output location. |
| Real `.env` is not read by default. | Real secrets must not be read unless explicitly approved. | Boundary plan marks `.env` as no access by default. | Pass | Good secret isolation. |
| `.env.example` may be read. | Template configuration may be inspected. | Boundary plan marks `.env.example` as read-only. | Pass | Placeholders only. |
| Secrets are never committed. | Repository must not contain real secrets. | Oracle module plan and boundary plan prohibit real secrets in repo. | Pass | Consistent with security rules. |
| Secrets are not logged. | Outputs and logs must redact secrets. | Boundary plan states secrets must not be logged and must be redacted in logs and outputs. | Pass | Adequate for design stage. |
| MCP tools have declared access levels. | Future tool boundaries should specify read/write access. | Boundary plan includes an MCP Tool Boundary table with access level and write permissions per tool. | Pass | No implementation is created. |
| Guide mode is read-only. | Guide mode must not write files. | `guide_mode` is defined as read-only. | Pass | Safe default mode. |
| Planning mode writes only Oracle docs. | Planning mode should write only `ai/oracle` plan/report docs. | `planning_mode` is scoped to `ai/oracle` plan/report docs. | Pass | No app or runtime writes. |
| KB assist mode does not write unless delegated. | KB assist should be advisory unless explicitly authorized. | `kb_assist_mode` suggests KB updates and does not write unless explicitly delegated. | Pass | Good separation from KB update skill. |
| Docker mount should start read-only. | First Docker runtime should mount repo read-only. | Boundary plan recommends starting with read-only Docker mount. | Pass | Correct safe runtime direction. |
| Failure conditions include app write attempt. | Writing `app/` should be a hard failure. | Failure conditions include tool attempts to write `app/`. | Pass | Correct blocker. |
| Failure conditions include secret leak. | Secret leakage should be a hard failure. | Failure conditions include secret appearing in output and unauthorized `.env` access. | Pass | Correct blocker. |
| No implementation was created. | No Dockerfile, MCP code, script, or automation should exist from this step. | Source documents are planning/validation docs only; this validation creates no runtime code. | Pass | Scope remains documentation-only. |

## Boundary Risks

- Future MCP implementation could accidentally expand write access if the declared access table is not enforced.
- Future Docker configuration could mount the repository writable too early.
- Future tool logs could expose secrets if redaction is not implemented before real secrets are introduced.
- `ai/domain-kb/` write access must remain explicit and tied to an approved KB update skill.
- `ai/gitnexus/` write access needs a separate policy before implementation starts.
- `writable/` may contain runtime-sensitive data and should stay read-only or inaccessible by default.

## Required Fixes Before Docker/MCP Runtime Design

No blocking fixes are required before Docker/MCP runtime design.

Required cautions for the next design phase:

- Define a concrete Docker mount policy before writing any Dockerfile.
- Define MCP tool schemas with access level metadata.
- Define output redaction rules before any tool can inspect environment-related files.
- Define approved writable output directories before report-writing tools are implemented.

## Final Verdict

PASS: Ready for Oracle Docker/MCP runtime design.
