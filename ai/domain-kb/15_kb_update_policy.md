# 15 KB Update Policy

## Purpose

Define how the Knowledge Base is updated when the repository changes.

This policy is documentation-only. It defines expected KB update behavior for future automation, Oracle MCP guidance, GitNexus task linkage, and human review.

## Core Principle

- KB must always reflect the current repository state.
- Any code change that affects a domain must trigger a KB update.
- If a KB update is not required, that decision must be explicit and source-backed.
- The repository is the source of truth; KB files are the operational memory layer that must stay synchronized with it.

## When KB Update is Required

KB update is required when a change affects repository behavior, domain ownership, security posture, route mapping, schema structure, or user-visible workflow.

Required update triggers:

- Route changes:
  - New route added.
  - Route removed.
  - URI, HTTP method, route group, route filter, route name, controller, or controller method changed.
  - Public route becomes protected or protected route becomes public.
  - Route permission changes.

- Controller changes:
  - New controller or controller method added.
  - Controller behavior changes user/admin flow.
  - Controller renders a new view or stops rendering an existing view.
  - Controller starts or stops enforcing permission checks.

- Service logic changes:
  - Business rules change.
  - Service starts using a different model, table, permission, route flow, or external output.
  - Service introduces a new domain behavior that should be discoverable from the KB.

- Model changes:
  - Model table mapping changes.
  - Allowed fields, validation rules, timestamps, soft-delete behavior, or relationships change.
  - New model added or existing model removed.

- Migration/schema changes:
  - Table added, removed, or renamed.
  - Column added, removed, renamed, or behavior changed.
  - Index, unique constraint, foreign key, or seed-required field changes.
  - Seeder introduces domain data or permissions.

- Permission/RBAC changes:
  - New permission added.
  - Route filter argument changes.
  - Role behavior changes.
  - Permission enforcement moves between filter, controller, service, or model.
  - Secretary/admin/user access policy changes.

- New feature addition:
  - New feature has routes, controllers, views, services, models, migrations, or permissions.
  - New feature introduces a new domain or changes an existing domain boundary.

- UI that implies backend change:
  - UI exposes cart, checkout, favorites, review, payment, account, or admin actions.
  - UI suggests a flow that does not yet have a mapped backend route.
  - UI connects to a controller/service that was not previously documented.

- Security-related changes:
  - Auth, session, CSRF, secure headers, filters, permissions, admin access, secretary access, checkout/order/payment, or user account behavior changes.
  - Any public/private boundary changes.

## Mapping Logic (CRITICAL)

KB update impact must be derived through the manifest.

Required mapping chain:

```text
Changed file path -> kb-manifest.yaml -> affected domain -> affected KB files
```

Process:

1. Collect changed paths.
2. Match changed paths against `kb-manifest.yaml`.
3. Check `watched_paths.exact` first.
4. Check `watched_paths.globs` second.
5. Check `watched_paths.broad_review` last.
6. Identify affected domain IDs and domain names.
7. Identify affected `kb_files`.
8. Update or explicitly skip each affected KB file with a reason.

Path signal strength:

| Manifest Path Type | Meaning | Expected Action |
|--------------------|---------|-----------------|
| `exact` | Strong domain ownership signal. | KB review is required; KB update is usually required. |
| `globs` | Medium domain ownership signal. | KB review is required; update depends on behavior impact. |
| `broad_review` | Weak or noisy review signal. | Human or Oracle MCP review should decide whether KB update is required. |

If a changed path does not match the manifest, the manifest itself may need an update.

## KB Update Workflow

1. Change detected.
   - Collect changed file paths.
   - Identify whether the change affects application behavior, documentation, schema, route, security, or domain ownership.

2. Identify affected domain.
   - Use `kb-manifest.yaml`.
   - Match changed paths against `watched_paths.exact`, `watched_paths.globs`, and `watched_paths.broad_review`.
   - Record all affected domains, not only the first match.

3. Identify affected KB files.
   - Read the matched domain's `kb_files`.
   - Include global KB files when the change affects repo-wide policy, manifest structure, route baseline, schema baseline, or claim registry.

4. Update KB files.
   - Update domain summaries when domain behavior changes.
   - Update route/security/schema files when relevant source files change.
   - Keep file contents repo-first and source-backed.
   - Mark uncertain areas with `Assumption` or `Needs Review`.

5. Update claim registry if needed.
   - Add a new claim ID for new automation-relevant findings.
   - Update status or confidence for existing claim IDs if evidence changes.
   - Do not silently overwrite or repurpose a claim ID.

6. Update route baseline if needed.
   - Required when `app/Config/Routes.php`, route filters, route permissions, or route controller mappings change.
   - Grouped rows must remain marked as not suitable for exact drift automation until extraction is automated.

7. Mark KB update as completed.
   - In GitNexus metadata, set `kb_update_status` to `completed`.
   - If no KB update is required, set status to `skipped` with a reason.
   - Link the validation report when available.

## Required KB Update Outputs

Minimum files to review or update during a KB update:

- Domain index:
  - Required if a domain is affected.
  - File: `ai/domain-kb/01_domain_index.md`.

- Route matrix:
  - Required if routing, route filters, controller route mapping, or access policy changes.
  - File: `ai/domain-kb/02_route_permission_matrix.md`.

- Security audit:
  - Required if auth, session, CSRF, secure headers, filters, RBAC, admin access, secretary access, user account access, order, checkout, or payment security changes.
  - File: `ai/domain-kb/03_security_filter_audit.md`.

- Route baseline:
  - Required if routes or route protection change.
  - File: `ai/domain-kb/06_route_baseline.md`.

- Claim registry:
  - Required if a new automation-relevant claim is added or an existing claim status/confidence changes.
  - File: `ai/domain-kb/09_claim_id_registry.md`.

- Schema/model matrix:
  - Required if models, migrations, seeders, tables, or schema ownership changes.
  - File: `ai/domain-kb/10_schema_model_matrix.md`.

- Manifest:
  - Required if a new KB file is added, a domain is added/renamed, watched paths change, or KB ownership changes.
  - File: `ai/domain-kb/kb-manifest.yaml`.

## Claim Update Rules

- New findings must create new claims when they are automation-relevant.
- Existing claims must not be overwritten silently.
- Claim IDs must remain stable after creation.
- If a claim changes meaning, create a new claim ID and mark the old claim as obsolete or superseded.
- Confidence must be updated if evidence changes.
- Claim status must use a consistent vocabulary:
  - `Verified`
  - `Partially Verified`
  - `Not Verified`
  - `Incorrect`
  - `Needs Review`
  - `Resolved`
  - `Partially Resolved`
- Every claim must include source anchors.
- Source anchors should prefer exact file paths, route patterns, model names, table names, permission names, and KB file paths.

## Drift Prevention Strategy

KB drift means repository code changed but the KB was not updated or explicitly marked as not requiring an update.

Drift can occur when:

- Route behavior changes without updating the route matrix or baseline.
- Model/table behavior changes without updating the schema/model matrix.
- Permission or filter behavior changes without updating security audit files.
- UI introduces a user flow without backend route mapping.
- A new domain file is added but the manifest does not watch it.
- A KB claim remains marked `Verified` after repository evidence changes.

Detection strategy:

1. Compare changed paths against `kb-manifest.yaml`.
2. Identify affected domains and KB files.
3. Check whether affected KB files changed in the same task/branch.
4. If no KB files changed, require an explicit `kb-update-not-required` or `skipped` reason.
5. For route changes, compare current routes against the route baseline.
6. For schema changes, compare model/migration evidence against the schema/model matrix.
7. For security-sensitive changes, require a security audit review.

Future integration:

- CI can block merges when changed paths match manifest domains but no KB update status is recorded.
- Oracle MCP can review affected domains and propose required KB files.
- GitNexus can keep `kb-update-required` labels active until KB update is completed or explicitly skipped.

## GitNexus Integration (Future)

Every task should include:

- `task_id`
- `affected_paths`
- affected domains
- affected KB files
- `kb_update_required`
- `kb_update_status`
- validation report path when available

Task rule:

- Task must include affected paths.
- Task must include affected domains.
- KB update must be linked to `task_id`.
- If `affected_paths` match manifest watched paths, GitNexus must mark the task `kb-update-required` until the KB update is completed or explicitly skipped.

Commit rule:

- Commit should be linked to `task_id` when possible.
- Commit metadata should reflect KB update status.
- If code and KB changes are related, they should occur in the same branch.
- If KB update is skipped, commit/task metadata must include the skip reason.

## Allowed Exceptions

KB update can be skipped only with an explicit reason.

Allowed exception categories:

- Pure refactor with no behavior change:
  - No route, service behavior, model/table mapping, permission, UI flow, or security behavior changes.

- Test-only changes:
  - Test files change without changing application behavior or KB claims.

- Dead code removal:
  - Removed code is confirmed unused, unrouteable, and not referenced by current KB claims.

Even in these cases, the task must explicitly mark:

```text
kb-update-status: skipped
reason: kb-update-not-required
```

If there is uncertainty, use `Needs Review` instead of skipping.

## Failure Conditions

The following conditions should fail KB update validation:

- Code changed but KB was not updated and no skip reason exists.
- A domain was impacted but no affected KB file was modified or reviewed.
- `kb-manifest.yaml` does not include a new KB file.
- A new domain appears in code but is not mapped in the manifest.
- A route changes without updating route matrix and route baseline.
- A filter, permission, or auth behavior changes without updating the security audit.
- A model, migration, seeder, or table mapping changes without updating the schema/model matrix.
- A claim contradicts source evidence.
- A claim confidence remains high after its evidence becomes uncertain.
- Manifest watched paths are missing for a newly introduced domain file.

## Status Model

KB update status values:

- `pending`
  - KB impact is known, but no update has started.

- `in_progress`
  - KB update is being prepared or reviewed.

- `completed`
  - Required KB files were updated and validated.

- `skipped`
  - KB update was not required and a reason is recorded.

Recommended GitNexus metadata field:

```text
kb_update_status: pending | in_progress | completed | skipped
```

Skipped status must always include a reason.

## Final Summary

- Policy enforces KB consistency.
- Policy makes repository changes traceable to domain documentation.
- Policy enables future KB update automation.
- Policy prepares the KB system for Oracle MCP review and GitNexus task/commit linkage.
- Policy does not replace route extraction, schema diffing, or CI validation; it defines the rules those systems should enforce.
