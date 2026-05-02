# 61 AI Workspace Full Discovery Report

## Purpose

Perform a full discovery and architecture audit of the current `ai/` workspace.

This report is documentation-only. It does not modify code, delete or move files, run Docker, modify `app/`, modify Domain KB, or create a Git commit.

## Top-Level Folder Map

| Path | Current Role | Current State | Notes |
|------|--------------|---------------|------|
| `ai/` | AI working layer root | Active | Contains root project context, rules, architecture, backlog, Domain KB, Oracle MCP docs/runtime, epics, tasks, prompts, and skills. |
| `ai/domain-cube/` | Original repository inventory source | Legacy / source archive | Contains the first inventory file. It is referenced by Domain KB manifest as `source_inventory`. |
| `ai/domain-kb/` | Domain Knowledge Base | Active / source-of-truth | Contains normalized KB files, KB update policy, route/security/schema baselines, GitNexus contract docs, manifest, and update reports. |
| `ai/domain-kb/updates/` | KB update test reports | Active evidence archive | Contains dry-run and real KB update test reports. |
| `ai/epics/` | Product/domain epics | Active planning reference | Contains broad epic docs for admin, storefront, catalog, RBAC, payment, secretary, and digital content. |
| `ai/oracle/` | Oracle module planning, validation, and runtime docs | Active | Contains Oracle planning docs, Docker/MCP boundary docs, validation reports, tool plans, reviews, and capability report. |
| `ai/oracle/runtime/` | Local Oracle MCP runtime | Active / partial implementation | Contains Dockerfile, Compose, runtime placeholder, registry, implemented read-only tools, and tool implementation reports. |
| `ai/oracle/runtime/tools/` | Oracle read-only lookup tools | Active implementation | Contains implemented tools for repo file, route, model, controller, permission, and filter lookup. |
| `ai/prompts/` | Reusable Codex prompt templates | Active | Contains implementation, review, and security prompt templates. |
| `ai/skills/` | Reusable local AI working rules | Active | Contains project-specific implementation rules for CI4 patterns, RBAC, migrations, audit logs, and output format. |
| `ai/tasks/` | Task backlog and executable task specs | Active / mixed freshness | Contains admin, secretary, shared, user, and archived tasks. |
| `ai/tasks/archive/` | Archived legacy tasks | Archived | Already separates old epics/user tasks from active task folders. |
| `ai/gitnexus/` | Future GitNexus implementation location | Missing | GitNexus contract exists in Domain KB, but no implementation folder exists yet. |
| `ai/plans/` | Future plan output location | Missing | No separate plan store currently exists. Planning exists as docs under Domain KB, Oracle, tasks, and epics. |

## Important Existing Systems Already Built

### Domain KB

Status: Complete enough for policy and manual update flow; partial for automation.

Built assets:

- `ai/domain-kb/kb-manifest.yaml`
- `00_repo_inventory.md` through `25_gitnexus_contract_readiness.md`
- Route, security, schema/model, claim registry, and manifest/schema planning docs
- KB update policy and KB update skill design docs
- KB update implementation plan and validation
- Real KB update test reports under `ai/domain-kb/updates/`

Evidence:

- `20_kb_update_system_test_summary.md` reports PASS for controlled KB update tests.
- `25_gitnexus_contract_readiness.md` reports PASS for Domain KB-side GitNexus contract readiness.

Conclusion:

- Domain KB update skill exists as policy/design/tested manual process.
- A fully automated KB update skill implementation does not exist yet.

### GitNexus

Status: Contract complete; implementation missing.

Built assets:

- `13_gitnexus_metadata_baseline.md`
- `21_gitnexus_system_design.md`
- `22_gitnexus_design_validation.md`
- `23_gitnexus_workflow_policy.md`
- `24_gitnexus_workflow_policy_validation.md`
- `25_gitnexus_contract_readiness.md`

Missing assets:

- `ai/gitnexus/`
- GitNexus task store
- machine-readable task schema
- changed-path extractor
- branch/commit checker
- GitNexus MCP
- CI gate

Conclusion:

- GitNexus exists as a Domain KB contract and workflow policy.
- GitNexus implementation has not started.

### Oracle MCP

Status: Active partial implementation.

Built assets:

- Planning and boundary docs: `00` through `11`
- Local readiness and blocker docs: `12` through `18`
- Runtime folder and Docker design/validation docs: `20` through `32`
- Runtime structure docs: `33`, `34`, `36`, `37`
- Tool plans and implementation reports: `38`, `39`, `41`, `42`, `43`, `44`, `46`, `47`, `54`, `55`, `57`, `58`
- Reviews: `45`, `48`, `49`, `51`, `52`, `53`
- Capability report: `60_oracle_mcp_capability_report.md`
- Runtime files under `ai/oracle/runtime/`

Implemented runtime tools:

- `repo_file_lookup`
- `route_lookup`
- `model_lookup`
- `controller_lookup`
- `permission_lookup`
- `filter_lookup`

Important limitation:

- The runtime is not a real MCP protocol server yet.
- Current tools are local read-only Python tools behind a placeholder runtime entrypoint.
- Docker baseline was validated earlier, but current all-tool Docker validation after the latest tools still needs a separate controlled validation.

### Task Generators

Status: Not implemented.

Existing related assets:

- Task specs under `ai/tasks/`
- Prompt templates under `ai/prompts/`
- GitNexus metadata baseline under `ai/domain-kb/13_gitnexus_metadata_baseline.md`
- Oracle plans mention task and plan draft tools conceptually.

Conclusion:

- There is no executable task generator or task MCP yet.
- Task creation is currently manual and document-driven.

### Planning Docs

Status: Active but spread across multiple areas.

Locations:

- Root architecture/product docs under `ai/*.md`
- Domain KB planning under `ai/domain-kb/`
- Oracle planning under `ai/oracle/`
- Epic docs under `ai/epics/`
- Task specs under `ai/tasks/`

Conclusion:

- Planning exists and is rich.
- There is no single `ai/plans/` implementation yet.

### architecture.md Systems

Status: Active source-of-truth for application architecture.

`ai/architecture.md` defines:

- Route -> Filter -> Controller -> DTO -> Service -> Model/Repository -> View flow
- Thin controller rule
- DTO and service responsibilities
- RBAC/security boundaries
- Admin, secretary, storefront, catalog, cart/checkout/order, inventory, shipping, review/favorites, dashboard builder, page builder, digital access, audit log, and workflow principles

Note:

- The file contains mojibake-style arrow characters in several places. Meaning remains understandable, but cleanup should be done only in a controlled documentation task.

## Complete / Partial / Abandoned / Duplicate Assessment

| Area | Classification | Reason |
|------|----------------|--------|
| Root project docs | Complete / active | `project.md`, `architecture.md`, `rules.md`, `current_state.md`, and `decisions.md` form the practical project-level source of truth. |
| Root backlog | Partial / active | `backlock.md` appears to be a backlog file despite spelling mismatch with README's `backlog.md` reference. |
| Domain Cube | Legacy / source archive | Superseded by normalized Domain KB, but still referenced as source inventory. |
| Domain KB core | Complete / active | Normalized KB and policy set exists. |
| Domain KB automation | Partial | Manual update tests passed; no automated diff/CI implementation yet. |
| Domain KB update reports | Complete evidence archive | Dry-run and real-test reports are useful audit artifacts. |
| GitNexus contract | Complete / active | Contract and workflow policy exist in Domain KB. |
| GitNexus implementation | Missing | No `ai/gitnexus/` folder exists. |
| Oracle planning docs | Complete / active history | Extensive staged docs exist from planning to capability report. |
| Oracle runtime | Partial / active | Safe local runtime and read-only tools exist, but real MCP protocol and latest Docker validation are pending. |
| Oracle app-side RBAC/CSRF experiment docs | Legacy / caution archive | `50`, `51`, and `52` document an app-side experiment and revert. They should be retained as caution evidence, not used as active runtime direction. |
| Epics | Active planning reference | Broad product modules are documented. |
| Tasks | Active / mixed freshness | Active tasks exist, but many are dated before the newer Domain KB/Oracle/GitNexus governance. |
| Archived tasks | Archived | Already separated; no immediate action required. |
| Skills | Active | Useful project-specific guardrails. |
| Prompts | Active | Useful task execution templates. |
| `ai/plans/` | Missing | Planning is not centralized in a plan store yet. |

## Likely Source-of-Truth Files

### Project and Architecture Source of Truth

- `ai/project.md`
- `ai/architecture.md`
- `ai/rules.md`
- `ai/current_state.md`
- `ai/decisions.md`
- `ai/backlock.md`

### Domain KB Source of Truth

- `ai/domain-kb/kb-manifest.yaml`
- `ai/domain-kb/01_domain_index.md`
- `ai/domain-kb/02_route_permission_matrix.md`
- `ai/domain-kb/03_security_filter_audit.md`
- `ai/domain-kb/06_route_baseline.md`
- `ai/domain-kb/09_claim_id_registry.md`
- `ai/domain-kb/10_schema_model_matrix.md`
- `ai/domain-kb/15_kb_update_policy.md`
- `ai/domain-kb/16_kb_update_skill_design.md`
- `ai/domain-kb/20_kb_update_system_test_summary.md`
- `ai/domain-kb/23_gitnexus_workflow_policy.md`
- `ai/domain-kb/25_gitnexus_contract_readiness.md`

### Oracle Source of Truth

- `ai/oracle/04_oracle_docker_mcp_runtime_design.md`
- `ai/oracle/06_oracle_mcp_tool_schema_design.md`
- `ai/oracle/08_oracle_mcp_implementation_plan.md`
- `ai/oracle/25_oracle_mcp_docker_seccomp_acceptance.md`
- `ai/oracle/32_oracle_mcp_docker_placeholder_run_validation.md`
- `ai/oracle/53_oracle_mcp_runtime_stabilization_review.md`
- `ai/oracle/60_oracle_mcp_capability_report.md`
- `ai/oracle/runtime/README.md`
- `ai/oracle/runtime/registry.py`
- `ai/oracle/runtime/compose.yaml`
- `ai/oracle/runtime/Dockerfile`

### Task / Prompt / Skill Source of Truth

- `ai/prompts/IMPLEMENT_TASK.md`
- `ai/prompts/REVIEW_TASK.md`
- `ai/prompts/SECURITY_CHECK.md`
- `ai/skills/*.md`
- active files under `ai/tasks/admin/`
- active files under `ai/tasks/secretary/`
- active files under `ai/tasks/shared/`
- active files under `ai/tasks/user/`

## Files That Should Not Be Touched Casually

- `ai/rules.md`
- `ai/architecture.md`
- `ai/current_state.md`
- `ai/decisions.md`
- `ai/domain-kb/kb-manifest.yaml`
- `ai/domain-kb/15_kb_update_policy.md`
- `ai/domain-kb/23_gitnexus_workflow_policy.md`
- `ai/domain-kb/25_gitnexus_contract_readiness.md`
- `ai/oracle/runtime/compose.yaml`
- `ai/oracle/runtime/Dockerfile`
- `ai/oracle/runtime/registry.py`
- `ai/oracle/runtime/tools/*.py`
- `ai/oracle/25_oracle_mcp_docker_seccomp_acceptance.md`
- `ai/tasks/archive/**`

Reason:

- These files define safety rules, domain ownership, workflow contracts, runtime boundaries, or historical audit evidence. Edits should happen only through explicit, scoped tasks.

## Legacy Plans That Can Be Archived Later

Do not delete these now. Archive only after a dedicated cleanup plan.

Candidates:

- `ai/domain-cube/00_repo_inventory.md`: keep as source inventory until Domain KB no longer references it.
- Older Oracle readiness gate docs from `00` through `32`: can be moved to a future `ai/oracle/archive/` after `60_oracle_mcp_capability_report.md` is accepted as the current capability baseline.
- Oracle RBAC/CSRF experiment docs:
  - `ai/oracle/49_rbac_csrf_gap_fix_plan.md`
  - `ai/oracle/50_rbac_csrf_gap_fix_implementation.md`
  - `ai/oracle/51_admin_dashboard_403_diagnosis.md`
  - `ai/oracle/52_revert_rbac_csrf_gap_fix.md`
- Domain KB early validation docs that are superseded by later PASS reports:
  - `04_kb_quality_audit.md`
  - `07_kb_normalization_report.md`
  - `08_post_normalization_validation.md`
  - `11_blocker_resolution_report.md`
  - `12_final_kb_readiness_check.md`
- Dry-run reports under `ai/domain-kb/updates/` after real-test reports are accepted as primary evidence.

Important:

- Archiving should preserve relative references or add a redirect index. Do not move files casually because many docs cite earlier numbered files.

## Recommended Clean Architecture Going Forward

### Domain KB

Role:

- Source of truth for repository domain knowledge, route/security/schema baselines, claim IDs, KB update policy, and GitNexus contract expectations.

Recommended state:

- Keep in `ai/domain-kb/`.
- Maintain `kb-manifest.yaml` as the domain-to-path mapping authority.
- Use `updates/` for KB update reports.
- Avoid embedding implementation code.

### GitNexus

Role:

- Task, plan, branch, commit, validation, and KB update linkage system.

Recommended state:

- Create future implementation under `ai/gitnexus/`.
- Start with documentation and schema, not code.
- Consume Domain KB contract files rather than duplicating policy.
- First assets should likely be:
  - `ai/gitnexus/00_gitnexus_module_plan.md`
  - `ai/gitnexus/01_gitnexus_contract_mapping.md`
  - `ai/gitnexus/schemas/task.schema.json` later, only after design validation.

### Oracle MCP

Role:

- Read-only repository guide and evidence collector.

Recommended state:

- Keep implementation under `ai/oracle/runtime/`.
- Keep docs under `ai/oracle/`.
- Complete a controlled Docker validation after the latest implemented tools.
- Do not add app-write capabilities.
- Do not add secrets.
- Do not promote to real MCP protocol until runtime safety is revalidated.

### Orkestra MCP

Role:

- Future coordinator across Domain KB, GitNexus, Oracle MCP, Task Skill, Plan Skill, Tester Skill, and Commit Gate.

Recommended state:

- Not implemented yet.
- Should not be created before GitNexus implementation boundaries are clear.
- Suggested future location: `ai/orkestra/`.

### Task Skill

Role:

- Convert user intent or GitNexus task metadata into a safe, scoped task definition.

Recommended state:

- Should consume:
  - `ai/tasks/**`
  - `ai/prompts/IMPLEMENT_TASK.md`
  - `ai/domain-kb/kb-manifest.yaml`
  - GitNexus metadata when available
- Not implemented yet.

### Plan Skill

Role:

- Convert a task into an implementation plan with affected files, risk, test, and KB update expectations.

Recommended state:

- Should write future plan docs under `ai/plans/` after that folder is formally introduced.
- Not implemented yet.

### Tester Skill

Role:

- Define and execute appropriate validation steps for changed areas.

Recommended state:

- Should consume Domain KB, Oracle lookup evidence, task metadata, and project skills.
- Not implemented yet.

### Commit Gate

Role:

- Prevent commits when required task metadata, validation, KB update report, or approval is missing.

Recommended state:

- Should be designed after GitNexus task schema is defined.
- Should remain advisory/manual first before any automation or CI gate.
- Not implemented yet.

## Immediate Next Milestone

Recommended immediate milestone based on the real current state:

```text
Oracle MCP post-expansion Docker validation, then GitNexus MCP integration review.
```

Why:

- Oracle runtime now has six implemented read-only tools.
- Local Python validation exists for the latest tools.
- Docker validation exists for earlier runtime states, but not after the latest controller/model/tool expansion.
- `ai/gitnexus/` does not exist yet, even though the Domain KB-side GitNexus contract is complete.

Practical next sequence:

1. Create a post-expansion Docker validation report for the current Oracle runtime.
2. If Docker validation passes, freeze Oracle runtime as the read-only evidence provider baseline.
3. Create `ai/gitnexus/00_gitnexus_module_plan.md`.
4. Map GitNexus implementation design back to:
   - `ai/domain-kb/13_gitnexus_metadata_baseline.md`
   - `ai/domain-kb/23_gitnexus_workflow_policy.md`
   - `ai/domain-kb/25_gitnexus_contract_readiness.md`
   - `ai/oracle/60_oracle_mcp_capability_report.md`

## Risk List If Existing AI Assets Are Ignored

- Rebuilding systems that already exist as policy or design docs.
- Treating GitNexus as unplanned even though its Domain KB contract is already complete.
- Treating Oracle MCP as only a plan even though runtime tools now exist.
- Assuming KB update automation exists when only manual/tested policy exists.
- Modifying `app/` without respecting Domain KB route/security/schema findings.
- Duplicating task and plan formats instead of aligning with existing prompts and skills.
- Losing historical safety decisions around `.env`, Docker seccomp, read-only mounts, and no app-write boundaries.
- Reintroducing RBAC/CSRF changes without learning from the documented revert sequence.
- Moving or deleting numbered docs and breaking cross-document references.
- Ignoring `ai/rules.md` and causing uncontrolled refactors, permission leaks, migration risk, or encoding issues.
- Starting Orkestra too early before GitNexus has a concrete task/plan/commit data model.

## Practical Recommendations

- Keep Domain KB as the knowledge source of truth.
- Keep Oracle as the read-only repo evidence provider.
- Build GitNexus next as the task/plan/commit linkage layer.
- Introduce `ai/plans/` only when a plan schema is defined.
- Do not create Orkestra MCP until GitNexus and Oracle boundaries are stable.
- Archive legacy docs only through an explicit archive plan.
- Add a small index file later for `ai/oracle/` and `ai/domain-kb/` so humans can find current source-of-truth docs faster.
- Treat `ai/tasks/` as useful but older than the Domain KB/GitNexus/Oracle governance layer; update task format before generating many new tasks.

## Final Decision

AI Workspace Discovery Complete: YES
