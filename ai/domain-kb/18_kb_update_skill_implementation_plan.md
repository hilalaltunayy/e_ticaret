# 18 KB Update Skill Implementation Plan

## Purpose

Define how the future KB Update Skill should be implemented safely as a documentation-only automation.

## Current Stage

- This is an implementation plan only.
- No implementation exists yet.
- No Docker runtime is required at this stage.
- Docker may be considered later for MCP/runtime isolation.
- No script, workflow, MCP server, Docker image, or automation code is created by this document.

## Implementation Goal

The future KB Update Skill should:

- Given changed repository paths, identify affected domains and KB files.
- Update documentation-only KB files.
- Produce a KB update report.
- Never modify application code.
- Never modify files under `app/`.
- Keep KB updates repo-first, source-backed, and traceable to `kb-manifest.yaml`.

## Proposed Execution Modes

### Manual AI-Assisted Mode

- Developer gives changed paths to AI/Codex.
- AI reads `kb-manifest.yaml`.
- AI identifies affected domains and affected KB files.
- AI updates relevant KB files under `ai/domain-kb`.
- AI produces an update report.
- This is the recommended first implementation mode.

### Local Script Mode

- Future script reads changed files.
- Script matches changed paths against `kb-manifest.yaml`.
- Script produces affected domain and affected KB file lists.
- AI or developer performs documentation updates.
- This mode can reduce manual path-matching work but should not be the first required dependency.

### Local Docker Mode

- Future option only.
- Used when MCP or isolated tooling is needed.
- Docker container may run route/schema extractors later.
- Not required for first implementation.
- No Docker command, Dockerfile, image, compose file, or runtime configuration is required now.

### MCP Mode

- Future Oracle MCP integration.
- Out of scope for this document.
- This plan does not design Oracle MCP, GitNexus MCP, or Orchestrator MCP.

## Implementation Steps

1. Read `changed_paths`.
   - Input can come from the developer, task metadata, branch diff, or a future script.

2. Parse `kb-manifest.yaml`.
   - Read global KB files.
   - Read domain IDs, domain names, watched paths, route patterns, permissions, and `needs_review`.

3. Match changed paths to `exact`, `globs`, `broad_review`, and `needs_review`.
   - `exact` matches are high-confidence.
   - `globs` matches are medium-confidence.
   - `broad_review` matches require manual review.
   - `needs_review` matches cannot be auto-updated without confirmation.

4. Identify affected domains.
   - A single changed path can affect multiple domains.
   - All matched domains must be recorded.

5. Identify affected KB files.
   - Use each matched domain's `kb_files`.
   - Include global KB files when manifest, route baseline, schema/model matrix, claim registry, or policy files are affected.

6. Decide update status.
   - `pending`: KB impact is detected but not updated yet.
   - `in_progress`: KB update is being prepared.
   - `completed`: Required KB files were checked and updated or explicitly confirmed current.
   - `skipped`: KB update is not required and a reason is recorded.

7. Update affected KB files.
   - Only update files under `ai/domain-kb`.
   - Mark uncertainty as `Needs Review` or `Assumption`.
   - Do not modify application code.

8. Add or update claim IDs if new facts are introduced.
   - Use `ai/domain-kb/09_claim_id_registry.md`.
   - Do not reuse claim IDs.
   - Do not silently overwrite claim history.

9. Update route baseline if routes changed.
   - Use `ai/domain-kb/06_route_baseline.md`.
   - Route changes include URI, method, controller, filter, permission, route group, or public/private boundary changes.

10. Update schema/model matrix if models/migrations changed.
   - Use `ai/domain-kb/10_schema_model_matrix.md`.
   - Mark field-level uncertainty as `Needs Review` if no extractor exists.

11. Generate KB update report.
   - Use the report format defined below.
   - Record changed paths, matches, updates, skipped files, warnings, and final status.

## Classification Rules

### Controller Changes

- Controller route behavior changed:
  - Update route matrix and route baseline if route-controller behavior changed.
  - Update domain index if domain flow changed.
  - Update claim registry if a new automation-relevant fact appears.

- Controller view binding changed:
  - Update domain index and route matrix when a controller starts or stops rendering a view.
  - Mark view linkage as `Needs Review` if static evidence is unclear.

- Controller only formatting changed:
  - KB update can be skipped if behavior, route mapping, permissions, views, and source-backed claims are unchanged.
  - Skip reason must be recorded.

- Controller dead code removed:
  - Update KB if the removed controller or method is referenced by a KB claim, route matrix, manifest watched path, or `needs_review`.
  - Skip only if the removed code is not route-bound and not referenced by current KB claims.

### Service Changes

- Business logic changed:
  - Update domain index.
  - Update claim registry if the change creates or changes an automation-relevant fact.
  - Update route/security/schema files if service behavior affects route access, security, model usage, or schema assumptions.

- Validation changed:
  - Update domain index if user/admin behavior changes.
  - Update security audit if validation affects auth, RBAC, order, checkout, payment, user account, or sensitive data.

- Permission-related logic changed:
  - Update security audit.
  - Update route matrix if route access expectations changed.
  - Update claim registry if a security or RBAC claim changes.

- Pure refactor:
  - KB update can be skipped only with explicit reason.
  - The report must state why behavior did not change.

### UI/View Changes

- UI-only copy/style change:
  - KB update can be skipped if no route, form action, backend expectation, permission, or domain behavior changed.
  - Skip reason must be recorded.

- UI implies new backend behavior:
  - Update domain index.
  - Update route baseline if a new route exists or is expected.
  - Mark as `Needs Review` if UI suggests backend behavior but no route/controller exists.

- Form action or route target changed:
  - Update route matrix.
  - Update route baseline.
  - Update security audit if auth, CSRF, permission, or sensitive action behavior is affected.

### Model/Migration Changes

- Table mapping changed:
  - Update schema/model matrix.
  - Update domain index if domain ownership changes.
  - Update claim registry if model/table claim status changes.

- Allowed fields changed:
  - Update schema/model matrix.
  - Mark field-level confidence based on source evidence.
  - Update security audit if sensitive fields are added or removed.

- New migration added:
  - Update schema/model matrix.
  - Update domain index if a new table, permission, seed expectation, or domain ownership appears.
  - Update manifest if new watched paths or new domains are introduced.

- Seeder changed:
  - Update schema/model matrix if seeded tables or domain seed ownership changed.
  - Update domain index if seeded permissions, page definitions, dashboard blocks, products, campaigns, coupons, users, or roles changed.

## Required Report Format

Future report path:

```text
ai/domain-kb/updates/YYYY-MM-DD_TASK-ID_kb_update_report.md
```

Required sections:

- Purpose
- Changed paths
- Matching results
- Affected domains
- Affected KB files
- Updates applied
- Claims added or updated
- Skipped updates
- Manual review items
- Final status

Minimum report fields:

- `task_id`
- `changed_paths`
- `affected_domains`
- `affected_kb_files`
- `updated_kb_files`
- `skipped_kb_files`
- `new_claim_ids`
- `warnings`
- `kb_update_status`

## Safety Rules

- Do not modify app code.
- Do not modify files under `app/`.
- Do not infer facts without source.
- Do not silently delete claims.
- Do not mark completed if affected KB files were not checked.
- Do not auto-update `needs_review` paths.
- Security-sensitive changes require explicit review.
- Documentation must remain English.
- All important findings must include source anchors.
- If evidence is uncertain, mark `Needs Review`.

## Docker Usage Notes

- Docker is not required for this phase.
- Docker may be useful later to run isolated extractors.
- Possible future Docker tools:
  - route extractor
  - schema/model extractor
  - KB lint checker
  - MCP server runtime
- No Docker command is required now.
- No Dockerfile or container configuration should be created at this stage.

## First Implementation Recommendation

Start with:

- Manual AI-assisted mode.
- No Docker.
- No MCP.
- Documentation-only updates.
- Validate with a fake `changed_paths` example.

Recommended first dry run:

```text
changed_paths:
  - app/Config/Routes.php
task_id: DRY-RUN-001
change_summary: Simulated route change for KB update skill validation.
```

Expected behavior:

- Match `app/Config/Routes.php` in manifest.
- Identify route-sensitive domains.
- Identify `02_route_permission_matrix.md`, `06_route_baseline.md`, and related KB files.
- Produce a dry-run update report.
- Do not modify application code.

## Test Scenarios

1. Route change example:
   - Changed path: `app/Config/Routes.php`
   - Expected KB files: route matrix, route baseline, security audit if access changes, claim registry if new route claim appears.

2. Filter/security change example:
   - Changed path: `app/Config/Filters.php`
   - Expected KB files: security audit, route matrix if access changes, route baseline if filter behavior changes, claim registry if security claim changes.

3. Model/migration change example:
   - Changed paths: `app/Models/OrderModel.php`, `app/Database/Migrations/*Order*.php`
   - Expected KB files: schema/model matrix, domain index, claim registry if model/schema claims change.

4. UI-only change example:
   - Changed path: `app/Views/site/products/product_detail.php`
   - Expected behavior: determine whether the change is copy/style only or implies backend flow such as favorites/review. Mark `Needs Review` if backend expectation is unclear.

5. Broad review path example:
   - Changed path: `app/Views/site/layouts/main.php`
   - Expected behavior: match `broad_review` or shared UI paths, require manual review, avoid marking completed automatically.

6. Needs review path example:
   - Changed path: `app/Models/RoleModels.php`
   - Expected behavior: detect `needs_review`, prevent auto-update completion, require explicit review and report warning.

## Out of Scope

- Oracle MCP.
- GitNexus MCP.
- Orchestrator MCP.
- GitHub Actions.
- Docker implementation.
- Application code changes.
- Runtime route extractor implementation.
- Runtime schema/model extractor implementation.
- CI enforcement implementation.
- Any script, workflow, Docker, MCP, or automation code.

## Final Summary

This implementation plan is ready for validation.

It recommends starting with manual AI-assisted mode, no Docker, no MCP, and documentation-only KB updates. Future local scripts, Docker runtime, MCP integration, route extraction, and schema extraction can be designed later after this plan is validated.
