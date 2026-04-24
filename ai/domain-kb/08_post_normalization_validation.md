# 08 Post-Normalization Validation

## Purpose

Validate whether the normalized Domain KB is now consistent, repo-accurate, and ready for KB update/drift automation.

This is an audit-only report. It does not modify application code and does not correct existing KB files.

## Scope

Reviewed sources:

- `ai/domain-kb/00_repo_inventory.md`
- `ai/domain-kb/01_domain_index.md`
- `ai/domain-kb/02_route_permission_matrix.md`
- `ai/domain-kb/03_security_filter_audit.md`
- `ai/domain-kb/04_kb_quality_audit.md`
- `ai/domain-kb/05_kb_manifest_and_schema_plan.md`
- `ai/domain-kb/06_route_baseline.md`
- `ai/domain-kb/07_kb_normalization_report.md`
- `ai/domain-kb/kb-manifest.yaml`
- `app/Config/Routes.php`
- `app/Config/Filters.php`

## Validation Checklist

| Check | Expected | Actual | Status | Notes |
|------|----------|--------|--------|------|
| Manifest is fully English. | No Turkish text, no mojibake, English reasons and assumptions. | Manifest content appears English and no mojibake was found in the reviewed file. | Pass | Source: `ai/domain-kb/kb-manifest.yaml`. |
| Manifest contains all KB files from 00 to 08. | Manifest should include `00` through `08`, plus `kb-manifest.yaml`. | Manifest includes `00` through `07` and `kb-manifest.yaml`, but does not include `08_post_normalization_validation.md`. | Fail | This report is intentionally not added because this task forbids editing existing KB files. |
| Manifest domain names are consistent. | Domain IDs and display names should match the domain vocabulary. | Most domain names are consistent; `campaign_coupon` exists in the manifest but is not a full first-class domain section in `01_domain_index.md`. | Partial | Campaign/Coupon separation is useful but not fully synchronized across KB files. |
| Manifest watched paths are separated into exact/globs/broad_review. | Each domain should use structured watched path groups. | Domains use `watched_paths.exact`, `watched_paths.globs`, and `watched_paths.broad_review`. | Pass | Source: `ai/domain-kb/kb-manifest.yaml`. |
| Uncertain paths are listed under needs_review. | Unclear or legacy paths should be grouped with reasons. | `needs_review` lists role model conflict, route-less controllers, unclear views, legacy layouts, and UI-only favorite/rating fragments. | Pass | Source: `ai/domain-kb/kb-manifest.yaml`. |
| Core KB files contain Purpose, Scope, Source of Truth, Key Claims, Assumptions, Risks, Related Files. | Core KB files should include all standard sections. | `00`, `01`, `02`, and `03` contain the expected standard sections. Later planning/audit files do not all follow this exact section list by design. | Partial | Core normalized files pass; not every supporting audit/planning file uses the same structure. |
| Important claims include source anchors. | Key claims should include source paths. | Key Claims sections include `Source` paths such as `app/Config/Routes.php`, `app/Config/Filters.php`, and KB file paths. | Pass | Source anchors are path-based, not line-based. |
| Claims use confidence values. | Claims should use `High`, `Medium`, or `Low`. | Key Claims in normalized files use confidence values. | Pass | Source: `00`, `01`, `02`, `03`, and `06`. |
| Route baseline exists. | `ai/domain-kb/06_route_baseline.md` should exist. | Route baseline exists. | Pass | Source: `ai/domain-kb/06_route_baseline.md`. |
| Route baseline matches `app/Config/Routes.php` as far as can be verified. | Public, auth, admin, secretary, campaign/coupon, and missing runtime route claims should match `Routes.php`. | Baseline matches major route groups and explicit route definitions. Some rows intentionally summarize multiple routes with wildcards. | Partial | Good for policy review; not yet a generated one-route-per-row baseline. |
| Security findings match `app/Config/Filters.php` as far as can be verified. | CSRF, secure headers, duplicate auth alias, and filter usage should match Filters.php. | CSRF and secure headers are aliases but not globally enabled; `auth` alias appears twice; route filters referenced in KB are configured as aliases. | Pass | Runtime environment overrides were not checked. |
| Missing runtime routes are clearly marked. | Cart, checkout, favorites, account, review, and payment route gaps should be explicit. | `06_route_baseline.md` clearly marks these as missing or unclear. | Pass | Source: `app/Config/Routes.php`, `ai/domain-kb/06_route_baseline.md`. |
| Remaining gaps are documented. | Normalization gaps and automation gaps should be listed. | `07_kb_normalization_report.md` documents remaining gaps, including route extractor, claim IDs, schema/model matrix, and Campaign/Coupon domain sync. | Pass | Source: `ai/domain-kb/07_kb_normalization_report.md`. |
| No application files were changed. | Only this validation report should be created for this task. | This audit did not modify application files. | Pass | No `app/` file edits were performed during this validation task. |
| KB appears ready or not ready for KB update automation. | Report should make an explicit readiness decision. | The KB is improved but still has blockers before automation. | Partial | See Automation Readiness Decision and Final Verdict. |

## Repository Accuracy Spot Check

### CSRF Finding

- Finding: CSRF is registered but not globally enabled.
- Source: `app/Config/Filters.php`.
- Status: Pass.
- Notes: The `csrf` alias exists, but `globals.before` has the CSRF entry commented out. Runtime or environment-specific overrides were not reviewed, so that part remains Needs Review.

### `/logout` Public Route Finding

- Finding: `/logout` is a public GET route.
- Source: `app/Config/Routes.php`.
- Status: Pass.
- Notes: The route is defined outside the `auth` group as `GET logout -> Logout::index`.

### Admin Route Protection Findings

- Finding: Admin-only route groups are protected by `role:admin`.
- Source: `app/Config/Routes.php`, `app/Config/Filters.php`.
- Status: Pass.
- Notes: Banners, dashboard builder, page builder, dashboard block APIs, marketing, pricing, automation, settings, and permission settings are inside the `role:admin` group.

- Finding: Shared admin/secretary operational routes use `role:admin,secretary|perm:*`.
- Source: `app/Config/Routes.php`.
- Status: Pass.
- Notes: Dashboard, stock, notifications, customers, products, shipping, shipping automation, and orders use role/permission route groups.

### Secretary Access Uncertainty

- Finding: Secretary access is permission-based for main operational areas but not for campaign/coupon routes.
- Source: `app/Config/Routes.php`, `app/Config/Filters.php`, `ai/domain-kb/03_security_filter_audit.md`.
- Status: Pass.
- Notes: Campaign/coupon routes use `campaign_access`, not the shared `role:admin,secretary|perm:*` pattern. Whether this is intentional policy remains Needs Review.

### Cart / Checkout / Favorites / Account / Review Route Gaps

- Finding: Runtime cart, checkout, favorites/wishlist, account/profile, review/rating, and payment routes are not visible in `Routes.php`.
- Source: `app/Config/Routes.php`, `ai/domain-kb/06_route_baseline.md`.
- Status: Pass.
- Notes: Builder routes exist for cart and checkout under admin page builder, but those are not runtime storefront commerce routes.

### Campaign/Coupon Domain Consistency

- Finding: Campaign/Coupon is now represented as a separate manifest domain, but the domain index does not yet contain a full `Domain: Campaign / Coupon` section.
- Source: `ai/domain-kb/kb-manifest.yaml`, `ai/domain-kb/01_domain_index.md`.
- Status: Partial.
- Notes: This is an intentional improvement direction from normalization, but the KB is not fully consistent until the domain index is updated.

## Automation Readiness Decision

- KB update skill readiness: Partially Ready
  - Reason: Manifest structure, source anchors, and claim schema are improved. However, the manifest does not include this `08` report, and stable claim IDs are not fully applied across all claims.

- KB drift check readiness: Partially Ready
  - Reason: Route baseline exists and major route/security facts match the repo. However, the baseline is manually maintained and still uses grouped wildcard rows.

- Oracle MCP readiness: Partially Ready
  - Reason: The KB is repo-first and useful for guidance. However, Campaign/Coupon domain consistency, schema/model mapping, and complete source anchoring still need improvement.

- GitNexus readiness: Partially Ready
  - Reason: Domain IDs and manifest mappings are clearer. However, GitNexus task labels, domain ownership metadata, and task-to-KB linkage rules are not yet defined.

## Blockers Before KB Update Automation

- Blocker:
  - Why it blocks automation: `kb-manifest.yaml` does not include `ai/domain-kb/08_post_normalization_validation.md`, so a strict manifest completeness check would fail immediately.
  - Required action: Update the manifest to include this report and define its role.

- Blocker:
  - Why it blocks automation: Campaign/Coupon exists as a manifest domain, but `01_domain_index.md` does not contain a matching first-class domain section.
  - Required action: Add or explicitly document Campaign/Coupon ownership in the domain index.

- Blocker:
  - Why it blocks automation: The route baseline is not generated and still groups multiple routes with wildcard rows.
  - Required action: Create a generated or one-route-per-row route baseline before strict drift automation.

- Blocker:
  - Why it blocks automation: Stable claim IDs are not yet applied across all important KB claims.
  - Required action: Add stable claim IDs to automation-facing claims.

- Blocker:
  - Why it blocks automation: Schema/model drift is not covered by a dedicated baseline.
  - Required action: Create `ai/domain-kb/08_schema_model_matrix.md` or a similarly named schema/model matrix after this validation report naming conflict is resolved.

## Non-Blocking Gaps

- Source anchors are path-based but not yet class/method-level everywhere.
- Runtime environment overrides for filters were not checked.
- Payment is not a first-class domain because no standalone payment route/controller was visible.
- Favorites/Wishlist and Review still need a UI-fragment versus backend-flow distinction.
- Some legacy or unclear files remain in `needs_review`.
- Commit-time validation rules are documented conceptually but not implemented.
- GitNexus labels and task linkage metadata are not yet defined.

## Final Verdict

- Final verdict: FAIL: Do not proceed before fixing blockers.
- Blocking issues:
  - Manifest does not include this `08` validation report.
  - Campaign/Coupon manifest-domain and domain-index structure are not fully aligned.
  - Route baseline is not yet strict enough for automated drift detection.
  - Stable claim IDs are incomplete.
  - Schema/model baseline is missing.
- Non-blocking issues:
  - Path-based anchors need class/method-level refinement.
  - Runtime filter overrides were not checked.
  - Payment, Favorites/Wishlist, and Review need deeper domain clarification.
  - GitNexus metadata is not yet defined.
- Recommended next step:
  - Fix KB structure blockers in documentation only, then create a dedicated schema/model matrix under the next available KB number.
