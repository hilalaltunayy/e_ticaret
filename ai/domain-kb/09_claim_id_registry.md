# 09 Claim ID Registry

## Purpose

Define stable claim IDs for automation-relevant KB claims.

This registry starts the stable claim ID system. It does not rewrite every historical claim in existing KB files; it creates the initial automation-facing claim set that future KB update and drift tooling can reference.

## Claim ID Format

Claim IDs should be stable, readable, and domain-oriented.

Examples:

- `AUTH-CLAIM-001`
- `ROUTE-CLAIM-001`
- `SECURITY-CLAIM-001`
- `DOMAIN-CLAIM-001`
- `MODEL-CLAIM-001`

Recommended prefixes:

- `AUTH-CLAIM-*` for login, logout, session, and auth flow claims.
- `ROUTE-CLAIM-*` for route baseline and route protection claims.
- `SECURITY-CLAIM-*` for CSRF, headers, RBAC, and filter claims.
- `DOMAIN-CLAIM-*` for domain ownership and domain readiness claims.
- `MODEL-CLAIM-*` for model, table, migration, and schema drift claims.

## Initial Claim Registry

| Claim ID | Domain | Claim Summary | Source KB File | Source Repo File | Confidence | Status |
|---------|--------|---------------|----------------|------------------|------------|--------|
| `SECURITY-CLAIM-001` | Security / Auth | CSRF global protection appears disabled. | `ai/domain-kb/03_security_filter_audit.md` | `app/Config/Filters.php` | High | Verified |
| `AUTH-CLAIM-001` | Auth | `/logout` appears public. | `ai/domain-kb/03_security_filter_audit.md`, `ai/domain-kb/06_route_baseline.md` | `app/Config/Routes.php` | High | Verified |
| `ROUTE-CLAIM-001` | Admin Panel | Admin routes are protected, but some areas may be role-only. | `ai/domain-kb/02_route_permission_matrix.md`, `ai/domain-kb/06_route_baseline.md` | `app/Config/Routes.php` | High | Verified |
| `SECURITY-CLAIM-002` | RBAC / Campaign / Coupon / Order | Permission enforcement is split across multiple filters and controller checks. | `ai/domain-kb/03_security_filter_audit.md` | `app/Config/Routes.php`, `app/Config/Filters.php` | Medium | Partially Verified |
| `ROUTE-CLAIM-002` | Cart | Cart runtime routes are missing or unclear. | `ai/domain-kb/06_route_baseline.md` | `app/Config/Routes.php` | High | Verified |
| `ROUTE-CLAIM-003` | Cart / Checkout / Order | Checkout runtime routes are missing or unclear. | `ai/domain-kb/06_route_baseline.md` | `app/Config/Routes.php` | High | Verified |
| `ROUTE-CLAIM-004` | Favorites / Wishlist | Favorites/Wishlist backend runtime flow is missing or unclear. | `ai/domain-kb/01_domain_index.md`, `ai/domain-kb/06_route_baseline.md` | `app/Config/Routes.php` | High | Verified |
| `ROUTE-CLAIM-005` | Review | Review backend runtime flow is missing or unclear. | `ai/domain-kb/01_domain_index.md`, `ai/domain-kb/06_route_baseline.md` | `app/Config/Routes.php` | High | Verified |
| `DOMAIN-CLAIM-001` | Campaign / Coupon | Campaign/Coupon is a distinct domain. | `ai/domain-kb/01_domain_index.md`, `ai/domain-kb/kb-manifest.yaml` | `app/Config/Routes.php`, `app/Config/Filters.php` | High | Verified |
| `ROUTE-CLAIM-006` | Route Baseline | Route baseline is partly manual and needs future automated extraction. | `ai/domain-kb/06_route_baseline.md` | `app/Config/Routes.php` | High | Verified |
| `MODEL-CLAIM-001` | Schema / Models | Schema/model matrix is missing from older KB files but now has an initial baseline in `10_schema_model_matrix.md`. | `ai/domain-kb/10_schema_model_matrix.md` | `app/Models/**`, `app/Database/Migrations/**` | High | Partially Resolved |
| `DOMAIN-CLAIM-002` | KB System | Stable claim IDs are not yet applied to every historical claim. | `ai/domain-kb/08_post_normalization_validation.md` | `ai/domain-kb/*.md` | High | Partially Resolved |
| `MODEL-CLAIM-002` | Product / Catalog | `ProductsModel.php` is the high-impact model owner for Product / Catalog in the optimized manifest. | `ai/domain-kb/01_domain_index.md`, `ai/domain-kb/10_schema_model_matrix.md`, `ai/domain-kb/kb-manifest.yaml` | `app/Models/ProductsModel.php` | High | Verified |
| `DOMAIN-CLAIM-003` | Frontend Storefront / Product / Catalog | Product site list view changes are high impact for Frontend Storefront and medium impact for Product / Catalog; backend-flow impact requires review when no diff is provided. | `ai/domain-kb/01_domain_index.md`, `ai/domain-kb/kb-manifest.yaml` | `app/Views/site/products/list.php` | Medium | Needs Review |
| `SECURITY-CLAIM-003` | Auth / User / Role / Permission / Secretary Access | `Filters.php` changes are high impact for Auth and User / Role / Permission, and medium impact for Secretary Access in the optimized manifest. | `ai/domain-kb/02_route_permission_matrix.md`, `ai/domain-kb/03_security_filter_audit.md`, `ai/domain-kb/kb-manifest.yaml` | `app/Config/Filters.php` | High | Verified |
| `ROUTE-CLAIM-007` | Auth / Admin Panel / Frontend Storefront / Page Builder / Dashboard Builder | `Routes.php` changes are classified by optimized manifest impact levels instead of triggering equal full-domain updates. | `ai/domain-kb/02_route_permission_matrix.md`, `ai/domain-kb/06_route_baseline.md`, `ai/domain-kb/kb-manifest.yaml` | `app/Config/Routes.php` | High | Verified |

## Usage Rules

- Claim IDs should not be renamed after they are referenced by automation.
- If a claim becomes obsolete, mark it as deprecated in this file instead of reusing the ID.
- New claims should use the next numeric ID in the relevant prefix group.
- Automation-facing KB files should reference these IDs where possible.

## Assumptions

- Assumption: The initial registry is intentionally small and focused on blockers from `08_post_normalization_validation.md`.
- Assumption: Historical claims will be migrated incrementally rather than rewritten all at once.

## Risks

- If future KB files introduce claims without IDs, automation may only partially validate KB drift.
- If claim wording changes but IDs remain stable, automation should rely on ID plus source anchors rather than exact prose.
