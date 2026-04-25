# 01 Oracle Module Plan Validation

## Purpose

Validate whether the Oracle module plan is safe, complete, and correctly scoped.

## Validation Checklist

| Check | Expected | Actual | Status | Notes |
|------|----------|--------|--------|------|
| Oracle is defined as AI repository guide. | Oracle should be positioned as a repository guide, not implementation code. | `00_oracle_module_plan.md` defines Oracle as the AI repository guide for the e-commerce project. | Pass | Correct role definition. |
| Oracle reads Domain KB. | Oracle should consume Domain KB files. | The plan lists `Read Domain KB` and defines Oracle as a domain knowledge reader. | Pass | Domain KB reading is a core responsibility. |
| Domain KB remains source of truth. | Oracle must not replace Domain KB. | The plan states Domain KB remains the source of truth for repository domains, claims, baselines, GitNexus contract rules, and KB update policy. | Pass | Clear authority boundary. |
| Oracle does not replace GitNexus. | GitNexus should own task, plan, branch, commit, validation, and KB update linkage. | The plan states GitNexus manages linkage and Oracle assists rather than replacing it. | Pass | Clear GitNexus separation. |
| Oracle supports task and plan creation. | Oracle should help draft tasks and plans. | The plan lists task/ticket and plan support, with `task_draft` and `plan_draft` outputs. | Pass | Support role is clear. |
| Oracle is read-only by default. | Oracle should not modify app code unless explicitly authorized. | The plan states Oracle must operate read-only unless explicitly authorized and must never modify app code directly in guide mode. | Pass | Good safety boundary. |
| Future MCP tools are listed but not implemented. | Tool names may be planned, but no implementation should exist. | Future MCP tools are listed under `Future MCP Tools`; no MCP implementation is created. | Pass | Correct planning-only scope. |
| Docker is future runtime direction, not implemented now. | Docker should be deferred. | The plan states Oracle MCP will eventually run locally in Docker and Docker is not implemented in the document. | Pass | Correct future runtime boundary. |
| Secrets must not be committed to repo. | Secret policy should prohibit real secrets in repository. | The plan says secrets must not be committed and real secrets must live in local `.env`, Docker secrets, or environment variables. | Pass | Strong baseline. |
| `.env.example` is allowed, real `.env` must stay local. | Placeholders are allowed; real `.env` stays outside commits. | The plan allows `.env.example`, says real `.env` must stay local, and notes `.env` should be added to `.gitignore` when implementation starts. | Pass | Adequate for planning stage. |
| App code changes are out of scope. | Oracle planning must not change application code. | App code changes are listed under Out of Scope. | Pass | Matches safety rule. |
| Orchestrator is out of scope. | Orchestrator implementation should be deferred. | Orchestrator implementation is listed under Out of Scope. | Pass | Correct separation. |

## Missing or Weak Areas

- Tool boundaries:
  - Status: Pass
  - Tool names are listed as future MCP tools only, and no tool behavior is implemented.
- Docker phase:
  - Status: Pass
  - Docker is clearly marked as future runtime direction. No Dockerfile or runtime implementation is included.
- Secret policy:
  - Status: Pass
  - Secret handling is strong for planning stage. Later implementation should add concrete `.gitignore`, `.env.example`, and secret loading rules.
- Oracle/GitNexus separation:
  - Status: Pass
  - Oracle assists task and plan creation, while GitNexus remains responsible for task/plan/commit linkage.
- Task/plan skill boundaries:
  - Status: Partial
  - The plan states Oracle can support task and plan creation, but detailed task/plan templates and approval boundaries should be defined in a later Oracle design document.

## Final Verdict

PASS: Ready for Oracle Docker/MCP runtime planning.

Recommended next file:

- `ai/oracle/02_oracle_runtime_boundary_plan.md`
