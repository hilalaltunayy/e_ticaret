# 04 KB Quality Audit

## Purpose

This document validates whether the current Domain KB is accurate against the repository, clear enough for maintainers, and ready to support future Oracle MCP, GitNexus, KB drift checks, and commit-time KB update automation.

The audit is documentation-only. It records verified facts, partial findings, uncertain claims, and automation blockers without changing application code or silently correcting existing KB files.

## Repository Accuracy Check

| KB Claim | Source KB File | Verified Against Repo | Status | Notes |
|---------|----------------|-----------------------|--------|-------|
| Public storefront routes exist for home, help page, login, register, logout, and product pages. | `02_route_permission_matrix.md`, `03_security_filter_audit.md` | `app/Config/Routes.php` | Verified | Public routes include `/`, `/yardim/(:segment)`, `/login`, `/login/auth`, `/register`, `/register/save`, `/logout`, `products/detail`, `products/list`, and `products/selection`. |
| Basic authenticated user routes are grouped behind the `auth` filter. | `02_route_permission_matrix.md`, `03_security_filter_audit.md` | `app/Config/Routes.php`, `app/Config/Filters.php` | Verified | `dashboard_anasayfa`, `products`, `orders`, and `orders/create` are inside a route group with `filter => auth`. |
| Admin-only routes are protected by `role:admin`. | `02_route_permission_matrix.md`, `03_security_filter_audit.md` | `app/Config/Routes.php`, `app/Config/Filters.php` | Verified | Banners, dashboard builder, page builder, dashboard blocks, marketing, pricing, automation, settings, and permission settings are grouped with `role:admin`. |
| Admin and secretary shared routes use both role and permission filters. | `02_route_permission_matrix.md`, `03_security_filter_audit.md` | `app/Config/Routes.php` | Verified | Shared groups use patterns such as `role:admin,secretary|perm:manage_orders`, `manage_products`, `manage_customers`, `manage_shipping`, `manage_stock`, `manage_notifications`, and `manage_dashboard`. |
| Campaign and coupon routes are protected by `campaign_access`. | `02_route_permission_matrix.md`, `03_security_filter_audit.md` | `app/Config/Routes.php`, `app/Config/Filters.php` | Verified | Campaign and coupon route groups use `campaign_access`; the filter alias exists in `Filters.php`. |
| Route matrix controller references are mostly valid. | `02_route_permission_matrix.md` | `app/Controllers/**` | Partially Verified | Spot checks confirm important methods on `Admin\Orders`, `OrderPackingActions`, `PageController`, `Shipping`, and other listed controllers. The matrix groups many routes with wildcards, so every route-method pair is not individually traceable. |
| Commented user and role admin routes reference controllers that are not available. | `00_repo_inventory.md`, `02_route_permission_matrix.md` | `app/Config/Routes.php`, `app/Controllers/Admin/**` | Verified | Routes for `Admin\Users` and `Admin\Roles` are commented out, and matching controllers were not found. |
| Product selection route delegates to the product listing flow. | `00_repo_inventory.md`, `01_domain_index.md` | `app/Controllers/ProductController.php`, `app/Views/site/products/**` | Verified | `ProductController::selection()` delegates to `index()`. The existing `site/products/product_selection.php` view is present but not clearly route-bound by this method. |
| `site/products/product_selection.php` appears not to be connected to a current route/controller flow. | `00_repo_inventory.md`, `02_route_permission_matrix.md` | `app/Config/Routes.php`, `app/Controllers/ProductController.php`, `app/Views/site/products/product_selection.php` | Verified | The view exists, but the public `products/selection` route reaches the listing flow rather than directly rendering this view. |
| Stock movement create redirects to stock management rather than rendering its own view. | `00_repo_inventory.md` | `app/Controllers/Admin/StockMove.php`, `app/Views/admin/orders/stock_management_view.php` | Verified | The create flow redirects to the stock management view with query parameters. |
| Manual stock correction is admin-only inside controller logic. | `00_repo_inventory.md`, `03_security_filter_audit.md` | `app/Controllers/Admin/StockMove.php` | Verified | Controller logic checks role before allowing `manuel_duzeltme`. This is controller-level enforcement in addition to route-level grouping. |
| CSRF is registered but not globally enabled. | `02_route_permission_matrix.md`, `03_security_filter_audit.md` | `app/Config/Filters.php` | Verified | `csrf` exists as an alias, but the global `before` list is empty/commented for CSRF. Assumption: no environment-specific override was checked. |
| Secure headers are registered but not globally enabled. | `03_security_filter_audit.md` | `app/Config/Filters.php` | Verified | `secureheaders` exists as an alias but is not applied in global filters. |
| `/logout` is public and not explicitly protected by `auth`. | `02_route_permission_matrix.md`, `03_security_filter_audit.md` | `app/Config/Routes.php` | Verified | The logout route is defined outside the authenticated group. |
| `auth` filter alias is duplicated. | `03_security_filter_audit.md` | `app/Config/Filters.php` | Verified | The alias array contains duplicate `auth` entries, which is a maintainability and ambiguity concern. |
| `RoleModel.php` and `RoleModels.php` possible conflict exists. | `00_repo_inventory.md`, `01_domain_index.md` | `app/Models/RoleModel.php`, `app/Models/RoleModels.php` | Verified | Both files declare a `RoleModel` class, making the KB concern valid. |
| `VisitModel` and `CreateVisitsTable` are misaligned. | `00_repo_inventory.md`, `01_domain_index.md` | `app/Models/VisitModel.php`, `app/Database/Migrations/CreateVisitsTable.php` | Verified | Migration includes `user_id`, while the model allowed fields are limited to `id` and `visited_at`. |
| Runtime cart and checkout routes are missing or unclear. | `00_repo_inventory.md`, `01_domain_index.md`, `02_route_permission_matrix.md` | `app/Config/Routes.php`, `app/Controllers/**`, `app/Views/admin/pages/**` | Partially Verified | Admin page builder files and seeded page codes exist for cart/checkout, but no clear public runtime cart or checkout route was found. |
| Favorites and wishlist backend flow is missing or unclear. | `00_repo_inventory.md`, `01_domain_index.md`, `02_route_permission_matrix.md` | `app/Config/Routes.php`, `app/Controllers/**`, `app/Models/**`, `app/Views/site/products/product_detail.php` | Partially Verified | No clear backend route/model/service/migration was found. However, the product detail view contains favorite UI elements, so the KB should distinguish UI fragments from backend flow. |
| Review/rating backend flow is missing or unclear. | `00_repo_inventory.md`, `01_domain_index.md`, `02_route_permission_matrix.md` | `app/Config/Routes.php`, `app/Controllers/**`, `app/Models/**`, `app/Views/site/products/product_detail.php` | Partially Verified | No clear review controller/model/service/migration was found. The product detail view contains rating display UI, so this should be recorded as UI-only or source-data unclear. |
| Payment flow is not mapped as a standalone runtime domain. | `00_repo_inventory.md`, `02_route_permission_matrix.md` | `app/Config/Routes.php`, `app/Controllers/**`, `app/Views/admin/orders/**` | Partially Verified | Order records and admin views include payment-related fields, but no standalone payment route/controller was identified in the reviewed route map. |
| Page builder is admin-only. | `01_domain_index.md`, `02_route_permission_matrix.md`, `03_security_filter_audit.md` | `app/Config/Routes.php`, `app/Controllers/Admin/PageController.php`, `app/Views/admin/pages/**` | Verified | Page builder routes are inside the `role:admin` group. The controller selects builder views dynamically based on page type. |
| Dashboard builder is admin-only. | `01_domain_index.md`, `02_route_permission_matrix.md`, `03_security_filter_audit.md` | `app/Config/Routes.php`, `app/Controllers/Admin/DashboardBuilder.php`, `app/Controllers/Admin/DashboardBlocks.php` | Verified | Dashboard builder and dashboard block routes are inside the `role:admin` group. |
| Migration and table assumptions are fully mapped by domain. | `00_repo_inventory.md`, `01_domain_index.md` | `app/Database/Migrations/**`, `app/Models/**` | Partially Verified | Several model/table relationships are documented, but a complete migration-to-model-to-domain matrix has not been built. |
| Permission names in the KB match route-level permission usage. | `01_domain_index.md`, `02_route_permission_matrix.md`, `03_security_filter_audit.md` | `app/Config/Routes.php` | Verified | Route-level permission names such as `manage_orders`, `manage_products`, `manage_shipping`, and `campaign_access` match current route filters. |
| Permission existence and seeding are fully verified. | `01_domain_index.md`, `02_route_permission_matrix.md` | `app/Database/Seeds/**`, `app/Database/Migrations/**` | Partially Verified | Route references are verified. A full permission seed/table inventory was not completed in this audit file. |
| `kb-manifest.yaml` is suitable as an English-only automation manifest. | `kb-manifest.yaml` | `ai/domain-kb/kb-manifest.yaml` | Incorrect | The manifest still contains Turkish/mojibake comments, `needs_review` reasons, and assumptions. |
| `kb-manifest.yaml` covers all current KB files. | `kb-manifest.yaml` | `ai/domain-kb/kb-manifest.yaml`, `ai/domain-kb/*.md` | Incorrect | The manifest references earlier KB files but does not include `02_route_permission_matrix.md`, `03_security_filter_audit.md`, or this audit file in its global KB file list. |
| Manifest watched paths are adequate for drift detection. | `kb-manifest.yaml` | `ai/domain-kb/kb-manifest.yaml`, repository paths | Partially Verified | Many watched paths point at real areas, but several are broad (`app/Controllers/Admin/**`, `app/Views/site/**`) and some future-oriented globs do not currently match concrete files. |

## KB Structure Quality

The current KB is separated into useful responsibilities:

- `00_repo_inventory.md` acts as the broad repository inventory.
- `01_domain_index.md` gives domain-level summaries.
- `02_route_permission_matrix.md` focuses on route, filter, controller, permission, and view relationships.
- `03_security_filter_audit.md` focuses on authentication, authorization, CSRF, logout, and filter enforcement.
- `kb-manifest.yaml` is intended to connect source paths to KB files for drift detection.

The separation is mostly maintainable, but the manifest is behind the rest of the KB. It does not list the newer route and security audit files, and it still contains non-English/mojibake text. This makes it less reliable as the automation entry point.

Domain names are mostly consistent across the Markdown files. However, Campaign/Coupon appears in route and security findings but is not represented as a first-class domain in `01_domain_index.md`. Cart, checkout, favorites, wishlist, review, and payment are also mixed between real backend gaps, admin builder configuration, seeded page concepts, and storefront UI fragments. These should be separated more explicitly before automation.

Assumptions are generally marked in the Markdown files. Risks are usually separated from confirmed facts. The weak point is that assumptions and confidence levels are not machine-readable, and some findings need more precise categories such as `verified_backend_missing`, `ui_fragment_exists`, `route_missing`, or `seeded_page_only`.

The watched paths in the manifest are useful as a starting point, but they are uneven. Some are exact file paths and others are broad directory globs. Broad paths can cause noisy drift alerts, while future-oriented globs can hide the fact that no current implementation exists.

## Best Practice Check

| Principle | Assessment | Notes |
|----------|------------|-------|
| Repo-first documentation | Mostly satisfied | Most claims are based on routes, filters, controllers, models, services, migrations, seeds, and views. A few claims still need stronger source anchors. |
| No unsupported assumptions | Partially satisfied | Assumptions are marked in Markdown, but some domain summaries still compress uncertain areas too aggressively. |
| Traceability from feature to route to controller to service to model to view | Partially satisfied | Route and controller traceability is good for major admin/user flows. Service, model, table, and view traceability is incomplete for several domains. |
| Security-sensitive areas explicitly mapped | Mostly satisfied | Auth, RBAC, admin, secretary, CSRF, logout, and sensitive admin tools are documented. Payment and user account security are less complete because runtime routes are unclear. |
| Domain ownership is clear | Partially satisfied | Core domains are clear. Campaign/Coupon, Cart/Checkout, Favorites/Wishlist, Review, Payment, and Theme/Media need sharper ownership boundaries. |
| KB is easy to update after commits | Partially satisfied | File structure helps, but the manifest is stale and lacks update rules for newer KB files. |
| KB is machine-readable enough for future automation | Not yet satisfied | Markdown tables and YAML are useful, but there is no stable schema, source anchor convention, confidence field, generated route baseline, or validation rule set. |
| Avoid duplicated or conflicting domain descriptions | Partially satisfied | There is some repetition between inventory, domain index, route matrix, and security audit. Repetition is manageable now but could drift without a claim registry or source anchor rules. |

## Automation Readiness

| Automation Target | Readiness | Reason | Required Improvement |
|------------------|-----------|--------|----------------------|
| KB update skill | Partially ready | The KB has a useful file split and domain vocabulary, but update triggers are incomplete and the manifest does not include all KB files. | Normalize the manifest, add all KB files, define domain IDs, and document which source changes require which KB updates. |
| KB drift check | Partially ready | Watched paths exist, but broad globs and stale entries would create noisy or incomplete drift results. | Add a stable schema, reduce overly broad globs, define glob semantics, and add generated route/controller/model baselines. |
| Oracle MCP repo guidance | Partially ready | The KB can guide high-level domain understanding, route protection, and security review. It is weaker for precise file-level recommendations. | Add source anchors, confidence/status fields, and exact feature-to-file ownership metadata. |
| GitNexus task/plan linkage | Partially ready | Domain names can map to task areas, but there are no stable task labels, ownership IDs, or dependency fields. | Add domain IDs, GitNexus labels, impacted KB files, and task planning metadata per domain. |
| Commit-time KB validation | Not ready | There is no enforceable schema, no route snapshot, no manifest sync validation, and no rule that determines when documentation must change. | Create a KB schema, route baseline, path-to-domain validation rules, and manifest consistency checks before wiring commit hooks. |

## Detected KB Problems

- Problem:
  - Affected KB file: `ai/domain-kb/kb-manifest.yaml`
  - Why it matters: The manifest still contains Turkish/mojibake text, so the KB is not fully English and not clean for automation parsing or human review.
  - Suggested documentation improvement: Convert all manifest comments, assumptions, and `needs_review` reasons to English, preserving meaning.

- Problem:
  - Affected KB file: `ai/domain-kb/kb-manifest.yaml`
  - Why it matters: The manifest does not list `02_route_permission_matrix.md`, `03_security_filter_audit.md`, or `04_kb_quality_audit.md` in the global KB file list, so drift automation would miss important documentation targets.
  - Suggested documentation improvement: Add every active KB file to manifest metadata and define which domains each file supports.

- Problem:
  - Affected KB file: `ai/domain-kb/02_route_permission_matrix.md`
  - Why it matters: Several route rows use wildcard or grouped route notation. This is readable for humans but not precise enough for automated route drift detection.
  - Suggested documentation improvement: Add a generated or manually maintained route inventory with one row per concrete route, method, route name, controller method, filter, and expected permission.

- Problem:
  - Affected KB file: `ai/domain-kb/00_repo_inventory.md`, `ai/domain-kb/01_domain_index.md`
  - Why it matters: Favorites/Wishlist and Review are documented mostly as missing backend areas, but product detail view contains favorite and rating UI fragments. Automation needs to distinguish missing backend flow from existing UI-only elements.
  - Suggested documentation improvement: Add explicit labels for `UI fragment exists`, `backend route missing`, `model missing`, and `runtime flow missing`.

- Problem:
  - Affected KB file: `ai/domain-kb/01_domain_index.md`, `ai/domain-kb/02_route_permission_matrix.md`, `ai/domain-kb/03_security_filter_audit.md`
  - Why it matters: Campaign/Coupon is security-relevant and route-protected, but it is not a first-class domain in the domain index. This weakens ownership and drift mapping.
  - Suggested documentation improvement: Either add Campaign/Coupon as a domain or document it as an explicit subdomain under the owning domain with watched paths and permissions.

- Problem:
  - Affected KB file: `ai/domain-kb/03_security_filter_audit.md`
  - Why it matters: Security findings are valid against `Filters.php` and `Routes.php`, but runtime environment overrides and controller-level authorization checks are not fully normalized into confidence levels.
  - Suggested documentation improvement: Add a confidence/status field for each security finding and separate route-level, controller-level, and runtime-config uncertainty.

- Problem:
  - Affected KB file: `ai/domain-kb/kb-manifest.yaml`
  - Why it matters: Broad watched paths such as `app/Controllers/Admin/**` and `app/Views/site/**` can trigger many unrelated KB updates, while exact files can miss related changes if ownership is incomplete.
  - Suggested documentation improvement: Split watched paths into `exact_paths`, `domain_globs`, and `broad_review_globs`, with separate automation behavior for each.

- Problem:
  - Affected KB file: `ai/domain-kb/00_repo_inventory.md`, `ai/domain-kb/01_domain_index.md`
  - Why it matters: Migration/table assumptions are not yet represented as a complete schema-to-model matrix.
  - Suggested documentation improvement: Add a dedicated schema/model matrix that maps migrations, tables, models, allowed fields, services, and owning domains.

- Problem:
  - Affected KB file: All current KB Markdown files
  - Why it matters: Most claims do not include source line anchors or stable claim IDs, making future automated verification harder.
  - Suggested documentation improvement: Introduce source references, stable claim IDs, and verification statuses for critical claims.

## Required KB Improvements Before Automation

1. Translate and sanitize `kb-manifest.yaml` so all content is English and free of mojibake.
2. Add `02_route_permission_matrix.md`, `03_security_filter_audit.md`, and `04_kb_quality_audit.md` to manifest metadata.
3. Define stable domain IDs and decide whether Campaign/Coupon and Payment are first-class domains or documented subdomains.
4. Replace wildcard-heavy route documentation with a concrete route inventory baseline.
5. Add source anchors or stable source references for critical route, filter, controller, model, service, view, migration, and seed claims.
6. Add machine-readable claim metadata such as status, confidence, source file, and owning domain.
7. Normalize watched paths into exact paths, domain globs, and broad review globs.
8. Document UI-only fragments separately from backend runtime flows for favorites, wishlist, reviews, cart, checkout, and payment.
9. Create a schema/model matrix for migrations, tables, models, allowed fields, and services.
10. Define automation validation rules for when a source change must update one or more KB files.

## Summary

- Accurate KB areas: Route groups, major filters, core admin/secretary permission routing, page builder access, dashboard builder access, public storefront routes, CSRF registration status, logout exposure, duplicate `auth` alias, `RoleModel.php` / `RoleModels.php` conflict, and `VisitModel` / visits migration mismatch.
- Weak KB areas: Manifest coverage, route matrix granularity, migration-to-model traceability, Campaign/Coupon ownership, Payment ownership, and UI-only versus backend-flow distinctions for cart, checkout, favorites, wishlist, and review.
- Incorrect or unverified claims: `kb-manifest.yaml` is not English-only, does not list all current KB files, and is not yet complete enough for automation. Favorites and Review findings need more nuance because storefront UI fragments exist even though backend runtime flow remains unclear. CSRF findings are verified against `Filters.php`, but environment/runtime overrides were not checked.
- Automation blockers: Stale manifest, no stable claim schema, no concrete route baseline, broad watched paths, missing source anchors, and incomplete schema/model mapping.
- Recommended next file: `ai/domain-kb/05_kb_manifest_and_schema_plan.md`
