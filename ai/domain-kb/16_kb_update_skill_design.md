# 16 KB Update Skill Design

## Purpose

Define the design of a future AI-assisted KB Update Skill that updates Domain KB files when repository changes occur.

## Scope

- This is a design document only.
- No runtime implementation exists yet.
- This skill updates documentation only.
- This skill must not modify application code.
- This skill works before Oracle MCP, GitNexus, or Orchestrator integration.
- This document does not design Oracle MCP, GitNexus MCP, or Orchestrator MCP.
- This document defines the KB Update Skill boundary, inputs, outputs, decision logic, and safety rules.

## Skill Responsibilities

The future KB Update Skill should:

- Read changed paths.
- Match paths against `kb-manifest.yaml`.
- Detect affected domains.
- Detect affected KB files.
- Decide whether KB update is required.
- Update relevant KB files.
- Update the claim registry when needed.
- Update the route baseline when route changes occur.
- Update the schema/model matrix when model, migration, or seeder changes occur.
- Produce an update report.

The skill must keep the KB repo-first and source-backed. It must not infer repository behavior without evidence.

## Skill Inputs

| Input | Description | Required? | Example |
|------|-------------|-----------|---------|
| `changed_paths` | List of changed repository paths. | Yes | `["app/Config/Routes.php"]` |
| `task_id` | Stable task identifier. | Recommended | `GNX-1234` |
| `task_title` | Human-readable task title. | Recommended | `Add checkout route` |
| `branch_name` | Branch associated with the change. | Recommended | `codex/checkout-flow` |
| `commit_hash` | Commit identifier if already available. | No | `abc1234` |
| `change_summary` | Short description of the repository change. | Yes | `Routes.php changed to add checkout route.` |
| `risk_level` | Risk level for the change. | Recommended | `medium` |
| `manual_override_reason` | Reason to skip or override default KB update behavior. | Required only for override | `Refactor only; no behavior change.` |

## Skill Outputs

| Output | Description | Required? | Example |
|--------|-------------|-----------|---------|
| `affected_domains` | Domains matched from manifest watched paths. | Yes | `["Auth", "Order"]` |
| `affected_kb_files` | KB files that should be reviewed or updated. | Yes | `["ai/domain-kb/02_route_permission_matrix.md"]` |
| `updated_kb_files` | KB files actually updated. | Yes | `["ai/domain-kb/06_route_baseline.md"]` |
| `skipped_kb_files` | KB files reviewed but not updated, with reason. | Yes when skipped | `["ai/domain-kb/01_domain_index.md: no domain behavior change"]` |
| `new_claim_ids` | New claim IDs created in the claim registry. | Required when claims added | `["ROUTE-CLAIM-007"]` |
| `update_status` | KB update state. | Yes | `completed` |
| `update_report_path` | Path to the generated KB update report. | Yes | `ai/domain-kb/updates/2026-04-24_GNX-1234_kb_update_report.md` |
| `warnings` | Uncertain or manual-review findings. | Yes when present | `["Changed path only matched broad_review."]` |

## Update Decision Logic

Decision chain:

```text
changed_paths
-> kb-manifest.yaml
-> affected domains
-> affected KB files
-> update required / not required
```

The skill should:

1. Normalize changed paths.
2. Load `kb-manifest.yaml`.
3. Match each path against domain `watched_paths`.
4. Build the affected domain set.
5. Build the affected KB file set.
6. Classify each match by path type: `exact`, `globs`, or `broad_review`.
7. Decide required KB updates based on the change type and affected files.
8. Apply documentation-only KB updates.
9. Generate an update report.

If the changed path is not mapped by the manifest, the skill should mark the result as `Needs Review` and include `kb-manifest.yaml` as an affected KB file.

## Domain Matching Rules

- `exact` paths have highest priority.
- `globs` have medium priority.
- `broad_review` requires manual review.
- `needs_review` cannot auto-update without confirmation.
- A single changed path can affect multiple domains.
- If multiple domains match, all matching domains must be reported.
- If the skill cannot determine domain ownership, it must mark the domain as `Needs Review`.

Path matching behavior:

| Match Type | Priority | Default Action |
|------------|----------|----------------|
| `exact` | High | Review and usually update affected KB files. |
| `globs` | Medium | Review affected KB files; update if behavior or ownership changed. |
| `broad_review` | Low | Require manual review before marking update completed. |
| `needs_review` | Manual | Do not auto-update without confirmation. |
| No match | Manual | Add warning and review manifest coverage. |

## Claim Handling Rules

- New repo facts require new claim IDs when they affect automation-relevant behavior.
- Existing claim updates must preserve history when risk or security is involved.
- Confidence must be adjusted if evidence changes.
- Claim IDs must be stable.
- Claim IDs must not be reused for different meanings.
- Claims must include source anchors.
- Uncertain claims must be marked `Needs Review` or include an `Assumption`.
- Historical claims must not be deleted silently.

Claim registry updates are required when:

- A new route/security/schema/domain fact becomes automation-relevant.
- A previous claim changes status.
- A previous claim changes confidence.
- A claim is superseded by new repository evidence.

## Route Change Handling

If `app/Config/Routes.php` changes, update:

- `ai/domain-kb/02_route_permission_matrix.md`
- `ai/domain-kb/06_route_baseline.md`
- `ai/domain-kb/09_claim_id_registry.md` if new route claims exist

The skill should also review:

- `ai/domain-kb/01_domain_index.md` if route ownership changes.
- `ai/domain-kb/03_security_filter_audit.md` if route access, auth, role, permission, public/private boundary, or security-sensitive behavior changes.
- `ai/domain-kb/kb-manifest.yaml` if a new route introduces a new domain or new KB ownership.

Grouped route rows in the route baseline must remain marked as not suitable for exact drift automation until route extraction is automated.

## Security / RBAC Change Handling

If `app/Config/Filters.php`, auth filters, role filters, permission filters, campaign access filters, or auth services change, update:

- `ai/domain-kb/03_security_filter_audit.md`
- `ai/domain-kb/02_route_permission_matrix.md` if route access changes
- `ai/domain-kb/09_claim_id_registry.md` if new security claims exist

The skill should also review:

- `ai/domain-kb/01_domain_index.md` if domain access policy changes.
- `ai/domain-kb/06_route_baseline.md` if route filters or permissions change.
- `ai/domain-kb/kb-manifest.yaml` if new filters or permissions introduce new watched paths.

Security-sensitive changes must not be skipped unless the reason is explicit and reviewed.

## Schema / Model Change Handling

If models, migrations, or seeders change, update:

- `ai/domain-kb/10_schema_model_matrix.md`
- `ai/domain-kb/01_domain_index.md` if domain ownership changes
- `ai/domain-kb/09_claim_id_registry.md` if new model/schema claims exist

The skill should also review:

- `ai/domain-kb/kb-manifest.yaml` if new model, migration, or seeder paths are not watched.
- `ai/domain-kb/03_security_filter_audit.md` if schema changes affect users, permissions, sessions, orders, payments, checkout, or other sensitive data.

Schema/model updates should be marked `Needs Review` when field-level changes cannot be verified from static evidence.

## Safe Operation Rules

- Never modify application code.
- Never modify files under `app/`.
- Never invent repo facts.
- Mark uncertainty as `Needs Review`.
- Do not delete historical claims silently.
- Do not mark update completed if affected KB files were not checked.
- Use `kb_update_status` values from `15_kb_update_policy.md`.
- Keep generated documentation in English.
- Keep source anchors for every important finding.
- Prefer repo evidence over assumptions.
- If a decision is uncertain, write an update report and leave status as `pending` or `in_progress`.

Allowed `kb_update_status` values:

- `pending`
- `in_progress`
- `completed`
- `skipped`

Skipped status requires a reason.

## Manual Override Rules

Manual override can skip a KB update only when the reason is explicit and source-backed.

Allowed skip cases:

- Pure refactor with no behavior change.
- Test-only change.
- Dead code removal that does not affect current KB claims.
- Documentation-only change that does not alter repository facts.

Required override fields:

- `manual_override_reason`
- affected paths reviewed
- affected domains reviewed
- skipped KB files
- reviewer or task reference when available

The override must be recorded in the update report.

If a changed path matches `broad_review` or `needs_review`, the skill must not auto-skip without confirmation.

## Update Report Format

Future KB update reports should use this path pattern:

```text
ai/domain-kb/updates/YYYY-MM-DD_<task-id>_kb_update_report.md
```

Required sections:

- Purpose
- Changed paths
- Affected domains
- Affected KB files
- Updates applied
- Claims added/updated
- Skipped updates
- Needs review
- Final status

Recommended final status values:

- `completed`
- `completed_with_warnings`
- `skipped`
- `needs_review`
- `failed`

## Failure Conditions

The skill must fail or return `Needs Review` when:

- Manifest cannot map changed paths.
- Required KB file is missing.
- Changed path only matches `broad_review`.
- Changed path is listed in `needs_review`.
- Security-sensitive change has no KB update.
- Schema change has no schema/model matrix update.
- Route change has no route baseline update.
- New claim has no stable claim ID.
- Affected KB files were not checked.
- Source evidence contradicts existing KB claims.
- The skill would need to modify application code to proceed.

## Example Flow

Changed path:

- `app/Config/Routes.php`

Expected affected files:

- `ai/domain-kb/02_route_permission_matrix.md`
- `ai/domain-kb/06_route_baseline.md`
- `ai/domain-kb/09_claim_id_registry.md`
- `ai/domain-kb/kb-manifest.yaml` if route-related KB files or domain ownership changed

Expected flow:

1. Match `app/Config/Routes.php` against `kb-manifest.yaml`.
2. Identify all domains that watch `Routes.php`.
3. Review route matrix and route baseline.
4. Add or update route claims if route behavior changed.
5. Review security audit if public/private, auth, role, or permission behavior changed.
6. Produce an update report.
7. Mark `kb_update_status` as `completed` only if all required KB files were checked.

## Final Summary

- This skill will enable consistent documentation updates after repository changes.
- This skill will reduce KB drift by mapping changed paths to domains and KB files.
- This skill will prepare the KB for later Oracle MCP, GitNexus, and Orchestrator integration without depending on those systems.
- This skill remains documentation-only and must not modify application code.
- Out of scope: runtime implementation, CI wiring, MCP implementation, route extraction code, schema extraction code, and GitNexus automation.
- Recommended next validation file: `ai/domain-kb/17_kb_update_skill_readiness_review.md`.
