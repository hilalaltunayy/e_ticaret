# 07 KB Normalization Report

## Purpose

Record the Domain KB normalization work completed for manifest cleanup, claim schema adoption, source anchoring, and route baseline creation.

## Files Updated

| File | Change Type | Notes |
|------|-------------|-------|
| `ai/domain-kb/00_repo_inventory.md` | Updated | Added Purpose, Source of Truth, Key Claims, Related Files, Assumptions, Risks, and Last Normalization Notes. |
| `ai/domain-kb/01_domain_index.md` | Updated | Added Purpose, Scope, Source of Truth, Key Claims, Related Files, Assumptions, Risks, and Last Normalization Notes. |
| `ai/domain-kb/02_route_permission_matrix.md` | Updated | Added Scope, Source of Truth, Key Claims, Related Files, Risks, and Last Normalization Notes. |
| `ai/domain-kb/03_security_filter_audit.md` | Updated | Added Scope, Source of Truth, Key Claims, Related Files, Risks, and Last Normalization Notes. |
| `ai/domain-kb/kb-manifest.yaml` | Rewritten | Normalized to English, added all current KB files, introduced version 2 structure, and separated exact paths, globs, and broad review paths. |

## Manifest Fixes Applied

- Converted manifest content to English.
- Removed Turkish/mojibake text.
- Added every current KB file to `global_kb_files`.
- Added `06_route_baseline.md` and `07_kb_normalization_report.md` to manifest metadata.
- Normalized domain IDs and names.
- Added `Campaign / Coupon` as a separate automation domain because route and security behavior is distinct.
- Reduced broad wildcard usage by separating watched paths into:
  - `exact`
  - `globs`
  - `broad_review`
- Moved uncertain paths to `needs_review` with English reasons.
- Added route patterns and permissions for tracked domains where statically visible.

## Claim Schema Applied

The main KB files now include `Key Claims` sections using the normalized shape:

- Claim
- Source
- Confidence
- Domain
- Related files

Confidence values use:

- High
- Medium
- Low

Files updated with claim schema:

- `ai/domain-kb/00_repo_inventory.md`
- `ai/domain-kb/01_domain_index.md`
- `ai/domain-kb/02_route_permission_matrix.md`
- `ai/domain-kb/03_security_filter_audit.md`
- `ai/domain-kb/06_route_baseline.md`

## Source Anchors Added

Source anchors were added for important claims using repository paths and KB paths.

Common anchors:

- `app/Config/Routes.php`
- `app/Config/Filters.php`
- `app/Controllers/**`
- `app/Models/**`
- `app/Services/**`
- `app/Database/Migrations/**`
- `app/Database/Seeds/**`
- `app/Views/**`
- `ai/domain-kb/00_repo_inventory.md`
- `ai/domain-kb/01_domain_index.md`
- `ai/domain-kb/02_route_permission_matrix.md`
- `ai/domain-kb/03_security_filter_audit.md`
- `ai/domain-kb/04_kb_quality_audit.md`
- `ai/domain-kb/05_kb_manifest_and_schema_plan.md`
- `ai/domain-kb/06_route_baseline.md`
- `ai/domain-kb/kb-manifest.yaml`

Line numbers were not used because stable file paths, route patterns, classes, methods, permissions, and domain IDs are better anchors for future drift automation.

## Route Baseline Created

Created:

- `ai/domain-kb/06_route_baseline.md`

The route baseline includes:

- Purpose, Scope, and Source of Truth.
- Key route claims.
- A route baseline table with route, method, controller, route group, filter, permission, domain, source, confidence, and notes.
- Public routes.
- Authenticated routes.
- Admin routes.
- Secretary routes.
- User runtime routes.
- Missing or unclear routes.
- Drift-relevant route update rules.

The baseline explicitly marks missing or unclear routes for:

- Cart
- Checkout
- Favorites / Wishlist
- Account / Profile
- Review / Rating
- Payment
- User order history separate from admin redirect

## Remaining Gaps

- The route baseline is still manually maintained and partially grouped. A future generated route extractor would provide stronger drift detection.
- Existing KB files do not yet include stable claim IDs for every claim.
- A schema/model matrix does not yet exist for migration-to-model drift checks.
- Campaign/Coupon is now tracked in the manifest, but `01_domain_index.md` still does not have a full first-class `Domain: Campaign / Coupon` section.
- Payment is still not represented as a first-class domain because no standalone payment route/controller was visible.
- Favorites/Wishlist and Review still need distinction between UI fragments and backend runtime implementation.
- Source anchors are path-based; class/method-level anchors are not yet fully normalized across all KB files.
- Existing quality audit findings about the old manifest are now historical and should be revisited in a future quality re-audit.

## Automation Readiness After Normalization

| Automation Target | Readiness | Reason |
|------------------|-----------|--------|
| KB update skill | Partially ready | Manifest and claim sections are now more structured, but stable claim IDs and update rules still need deeper coverage. |
| KB drift check | Partially ready | Route baseline exists, but it is not generated and still contains grouped route rows. |
| Oracle MCP repo guidance | Partially ready | Source anchors and domain ownership are clearer, but schema/model mapping and claim IDs are incomplete. |
| GitNexus task/plan linkage | Partially ready | Domain IDs are clearer in the manifest, but task labels and planning metadata are not yet defined. |
| Commit-time KB validation | Not ready | No validation script, machine-readable claim registry, or generated route/schema baseline exists yet. |

## Next Recommended Step

Create `ai/domain-kb/08_schema_model_matrix.md`.

Purpose:

- Map migrations to tables.
- Map models to tables.
- Compare model allowed fields with migration fields.
- Connect tables/models to domains.
- Record schema drift risks before building automated KB validation.

This should come before commit-time KB automation because route drift and schema drift are the two most important baseline checks for this repository.
