# 02 Route Permission Matrix

## Purpose

This file shows the route -> filter -> controller -> permission -> view relationship.
The goal is to understand whether admin / secretary / user access is actually protected at route level.

## Scope

This file summarizes route-to-permission behavior for access review. It is human-readable and policy-oriented; the concrete drift baseline now belongs in `ai/domain-kb/06_route_baseline.md`.

## Source of Truth

- `app/Config/Routes.php`
- `app/Config/Filters.php`
- `ai/domain-kb/00_repo_inventory.md`
- `ai/domain-kb/01_domain_index.md`
- `ai/domain-kb/kb-manifest.yaml`

## Key Claims

- Claim: Main admin/secretary operational routes are protected by `role:admin,secretary|perm:*` filters.
  - Source: `app/Config/Routes.php`
  - Confidence: High
  - Domain: Admin Panel / Secretary Access / Order / Product / Catalog / Shipping
  - Related files: `app/Config/Filters.php`, `ai/domain-kb/06_route_baseline.md`

- Claim: Page builder and dashboard builder edit tools are admin-only.
  - Source: `app/Config/Routes.php`
  - Confidence: High
  - Domain: Page Builder / Dashboard Builder
  - Related files: `app/Controllers/Admin/PageController.php`, `app/Controllers/Admin/DashboardBuilder.php`, `app/Controllers/Admin/DashboardBlockController.php`

- Claim: Public product browsing routes are intentionally outside the auth group.
  - Source: `app/Config/Routes.php`
  - Confidence: High
  - Domain: Product / Catalog / Frontend Storefront
  - Related files: `app/Controllers/ProductController.php`, `app/Views/site/products/**`

- Claim: Runtime cart, checkout, favorites, account, and review routes are not visible in the reviewed route map.
  - Source: `app/Config/Routes.php`
  - Confidence: High
  - Domain: Cart / Favorites / Wishlist / Review / Frontend Storefront
  - Related files: `ai/domain-kb/06_route_baseline.md`, `ai/domain-kb/03_security_filter_audit.md`

- Claim: Route-level permission enforcement is not the only authorization layer; some controller-level checks also exist.
  - Source: `app/Config/Routes.php`, `ai/domain-kb/00_repo_inventory.md`
  - Confidence: Medium
  - Domain: RBAC / Order / Product / Catalog
  - Related files: `app/Controllers/Admin/Orders.php`, `app/Controllers/Admin/StockMove.php`

## Related Files

- `ai/domain-kb/03_security_filter_audit.md`
- `ai/domain-kb/04_kb_quality_audit.md`
- `ai/domain-kb/05_kb_manifest_and_schema_plan.md`
- `ai/domain-kb/06_route_baseline.md`
- `ai/domain-kb/kb-manifest.yaml`

Status values:

- OK: Route, filter/guard, controller, and expected view/API/redirect relationship appear statically consistent.
- Missing Filter: The route looks sensitive but has no route-level filter.
- Missing Permission: The route is role-protected but lacks an expected domain-level permission.
- Controller Not Found: The route controller file/method was not found in static review.
- View Not Found: A view was expected for the route but no view file was found.
- Route Unclear: The route exists but the target behavior is not clear.
- Needs Review: It may work, but security, permission, or view behavior should be verified separately.

## Route Matrix

| Route / URI | HTTP Method | Route Name | Controller@Method | Filter / Guard | Expected Permission | Related View | Domain | Status | Notes |
|------------|-------------|------------|-------------------|----------------|---------------------|------------|--------|-------|-----|
| `/` | GET | - | `StorefrontController@home` | Public | None | `site/storefront/home` | Frontend Storefront | OK | Public storefront home |
| `/yardim/(:segment)` | GET | - | `StorefrontController@placeholder` | Public | None | `site/storefront/fallback_page` | Frontend Storefront | OK | Public placeholder/help page |
| `/login` | GET | - | `Login@index` | Public | None | `auth/login` | Auth | OK | Login form is public |
| `/login/auth` | POST | - | `Login@auth` | Public | None | None (redirect) | Auth | Needs Review | Login POST must be public; CSRF appears globally disabled |
| `/register` | GET | - | `Register@index` | Public | None | `auth/register` | Auth | OK | Register form is public |
| `/register/save` | POST | - | `Register@save` | Public | None | None (redirect) | Auth | Needs Review | Register POST must be public; CSRF appears globally disabled |
| `/logout` | GET | - | `Logout@index` | Public | None | None (redirect) | Auth | Needs Review | No auth filter; public GET logout behavior should be evaluated |
| `/dashboard_anasayfa` | GET | - | `Home@index` | `auth` | Login | `site/home/index` | Frontend Storefront / Auth | OK | Normal user dashboard after login |
| `/products` | GET | - | `ProductController@index` | `auth` | Login | `site/products/index` | Product / Catalog | OK | Inside auth group; public product list also exists through `/products/selection` |
| `/orders` | GET | - | `OrderController@index` | `auth` | Login | None (redirect to `admin/orders`) | Order | Needs Review | User route redirects to admin orders; admin route applies permission again |
| `/orders/create` | POST | - | `OrderController@create` | `auth` | Login | None (redirect) | Order | OK | Logged-in user can create a reserved order |
| `/products/detail/(:segment)` | GET | - | `ProductController@detail` | Public | None | `site/products/product_detail` or `site/storefront/fallback_page` | Product / Catalog / Storefront | OK | Public product detail |
| `/products/list/(:any)/(:any)` | GET | - | `ProductController@listByCategory` | Public | None | `site/products/index` | Product / Catalog / Storefront | OK | Public category filter |
| `/products/list/(:any)` | GET | - | `ProductController@listByType` | Public | None | `site/products/index` | Product / Catalog / Storefront | OK | Public type filter |
| `/products/selection` | GET | - | `ProductController@selection` | Public | None | `site/products/index` | Product / Catalog / Storefront | OK | `selection()` returns `index()`; `product_selection.php` is not route-bound |
| `/admin/banners*` | GET/POST | - | `Admin\Banners@*` | `role:admin` | Admin role | `admin/banners/index` or redirect | Theme / Media | OK | Admin-only banner management |
| `/admin/dashboard-builder*` | GET/POST | - | `Admin\DashboardBuilder@*` | `role:admin` | Admin role | `admin/dashboard_builder/index` or JSON | Dashboard Builder | OK | Secretary access is not allowed |
| `/admin/pages*` | GET/POST | - | `Admin\PageController@*` | `role:admin` | Admin role | `admin/pages/*` or redirect/JSON | Page Builder | OK | Admin-only page builder, drafts, versions, and blocks |
| `/admin/dashboard/blocks*` | GET/POST | - | `Admin\DashboardBlockController@*` | `role:admin` | Admin role | JSON/partial | Dashboard Builder | OK | Admin-only dashboard builder block API |
| `/admin/marketing` | GET | - | `Admin\Marketing@index` | `role:admin` | Admin role | `admin/marketing/index` | Admin Panel / Campaign / Coupon | Missing Permission | Admin-only overview; no granular marketing permission |
| `/admin/pricing*` | GET/POST | - | `Admin\Pricing@*` | `role:admin` | Admin role | `admin/pricing/*` or redirect | Product / Catalog | Missing Permission | Pricing is admin-only; no granular pricing permission |
| `/admin/automation` | GET | - | `Admin\Automation@index` | `role:admin` | Admin role | `admin/automation/index` | Admin Panel | Route Unclear | Domain scope is unclear |
| `/admin/settings` | GET | - | `Admin\Settings@index` | `role:admin` | Admin role | `admin/settings/index` | Admin Panel | OK | Admin-only settings |
| `/admin/settings/permissions*` | GET/POST | - | `Admin\SettingsPermissionsController@*` | `role:admin` | Admin role | `admin/settings/permissions` or redirect/JSON | Secretary Access / RBAC | OK | Secretary permission management is admin-only |
| `/admin/dashboard` | GET | - | `Admin\DashboardController@index` | `role:admin,secretary\|perm:manage_dashboard` | `manage_dashboard` for secretary | `admin/dashboard/index` | Dashboard Builder / Admin Panel | OK | Admin bypass; secretary requires permission |
| `/admin/stock*` | GET/POST | - | `Admin\Stock@*`, `Admin\StockMove@*` | `role:admin,secretary\|perm:manage_stock` | `manage_stock` for secretary | `admin/stock/index`, `admin/stock/moves`, or redirect | Product / Catalog | OK | `StockMove@store` also restricts `manuel_duzeltme` to admin |
| `/admin/notifications*` | GET/POST | - | `Admin\Notifications@*` | `role:admin,secretary\|perm:manage_notifications` | `manage_notifications` for secretary | `admin/notifications/index` or JSON/redirect | Admin Panel / Notification | OK | Notification tests and template writes are guarded |
| `/admin/customers` | GET | - | `Admin\Customers@index` | `role:admin,secretary\|perm:manage_customers` | `manage_customers` for secretary | `admin/customers/index` | User / Admin Panel | OK | Customer index only visible in route matrix |
| `/admin/products*` | GET/POST | - | `Admin\Products@*` | `role:admin,secretary\|perm:manage_products` | `manage_products` for secretary | `admin/products/*` or redirect/JSON | Product / Catalog | OK | Product CRUD and datatable are guarded |
| `/admin/authors*` | GET/POST | - | `Admin\Products@createAuthor/storeAuthor` | `role:admin,secretary\|perm:manage_products` | `manage_products` for secretary | `admin/authors/create` or redirect | Product / Catalog | OK | Author management is under product permission |
| `/admin/categories*` | GET/POST | - | `Admin\Products@createCategory/storeCategory` | `role:admin,secretary\|perm:manage_products` | `manage_products` for secretary | `admin/categories/create` or redirect | Category | OK | Category management is under product permission |
| `/admin/shipping*` | GET/POST | - | `Admin\Shipping@*`, `Admin\ShippingCompanies@*`, `Admin\ShippingAutomationController@*` | `role:admin,secretary\|perm:manage_shipping` | `manage_shipping` for secretary | `admin/shipping/*`, JSON, redirect, or file response | Shipping / Order | OK | Shipping, templates, bulk outputs, companies, and automation are guarded |
| `/admin/campaigns*` | GET/POST | - | `Admin\Campaigns@*` | `campaign_access` | `manage_campaigns_engine` or `manage_campaigns` for admin | `admin/campaigns/*` or redirect | Campaign | OK | Filter requires admin role and campaign permission; secretary is blocked |
| `/admin/coupons*` | GET/POST | - | `Admin\Coupons@*` | `campaign_access` | `manage_campaigns_engine` or `manage_campaigns` for admin | `admin/coupons/*` or redirect | Cart / Coupon | OK | Filter requires admin role and campaign permission; secretary is blocked |
| `/admin/orders*` | GET/POST | - | `Admin\Orders@*`, `Admin\OrderStatuses@index` | `role:admin,secretary\|perm:manage_orders` | `manage_orders` for secretary | `admin/orders/*`, JSON, redirect, PDF/file | Order | OK | Order management, analytics, status, packing, invoice, shipping, cancel/return are guarded |

## Role-Based Access Summary

### Admin

- Routes that can be accessed:
  - All public routes: `/`, `/yardim/*`, `/login`, `/register`, public product routes.
  - Auth routes: `/dashboard_anasayfa`, `/products`, `/orders`, `/orders/create` after login.
  - Admin-only routes: banners, dashboard builder, page builder, marketing, pricing, automation, settings, secretary permissions.
  - Admin+secretary permission routes: dashboard, stock, notifications, customers, products, shipping, shipping automation, orders.
  - Campaign/coupon routes: `campaign_access` checks admin role and campaign permission; unlike `RoleFilter`, it does not simply rely on admin bypass.
- Routes that should not be accessed:
  - No statically defined admin-specific forbidden route was found.
- Risky/unclear routes:
  - `/admin/marketing` and `/admin/pricing*` are admin-only but lack granular permissions.
  - `/admin/automation` has unclear domain scope.
  - `/logout` is public GET.
  - Global CSRF appears disabled for login/register and other POST routes.

### Secretary

- Routes that can be accessed:
  - Redirect target after login: `admin/dashboard`.
  - With permission: `admin/dashboard`, `admin/stock*`, `admin/notifications*`, `admin/customers`, `admin/products*`, `admin/shipping*`, `admin/orders*`.
- Routes that should not be accessed:
  - Admin-only areas protected by `role:admin`: banners, dashboard builder, page builder, marketing, pricing, automation, settings, settings permissions.
  - Campaign/coupon routes under `campaign_access`; the filter requires role `admin`.
- Permission-based routes:
  - `manage_dashboard`: `/admin/dashboard`.
  - `manage_stock`: `/admin/stock*`.
  - `manage_notifications`: `/admin/notifications*`.
  - `manage_customers`: `/admin/customers`.
  - `manage_products`: `/admin/products*`, `/admin/authors*`, `/admin/categories*`.
  - `manage_shipping`: `/admin/shipping*`, `/admin/api/shipping`.
  - `manage_orders`: `/admin/orders*`, `/admin/api/orders*`.
- Risky/unclear routes:
  - Campaign/coupon access is blocked for secretary users; whether this is intended needs_review.
  - `StockMove@store` has an extra admin-only rule for `manuel_duzeltme`; route permission alone does not explain the full behavior.
  - Some `Admin\Orders` methods call `canManageOrders()` internally; permission checks are not in a single layer.

### User

- Routes that can be accessed:
  - Public storefront and product routes: `/`, `/yardim/*`, `/products/detail/*`, `/products/list/*`, `/products/selection`.
  - Login/register/logout routes.
  - After login: `/dashboard_anasayfa`, auth-group `/products`, `/orders`, `/orders/create`.
- Routes that should not be accessed:
  - All `/admin/*` routes.
- Routes requiring login:
  - `/dashboard_anasayfa`, `/products`, `/orders`, `/orders/create`.
- Public routes:
  - `/`, `/yardim/*`, `/login`, `/login/auth`, `/register`, `/register/save`, `/logout`, `/products/detail/*`, `/products/list/*`, `/products/selection`.
- Risky/unclear routes:
  - `/orders` requires login but redirects to `admin/orders`.
  - Runtime cart, checkout, favorites, account, and review routes are not visible.
  - `/logout` is public; whether it should require login needs review.

## Critical Access Controls

1. Does the admin dashboard route exist?
   - Yes. `GET /admin/dashboard` -> `Admin\DashboardController@index`.

2. Is the admin dashboard route protected by auth/filter?
   - Yes. It is under `role:admin,secretary|perm:manage_dashboard`.

3. Can secretary access the admin dashboard, or is it blocked?
   - Secretary can access it only with `manage_dashboard`. Admin bypasses permissions; secretary is checked through `UserPermissionModel::isAllowed`.

4. Does the secretary order page route exist?
   - Yes. `GET /admin/orders` -> `Admin\Orders@index`.

5. Is the secretary order page protected by permission?
   - Yes. The route group uses `role:admin,secretary|perm:manage_orders`.

6. Are user storefront pages public, and are login-required routes separated?
   - Public storefront routes are separated: `/`, `/yardim/*`, `/products/detail/*`, `/products/list/*`, `/products/selection`.
   - Login-required user routes are under the auth group: `/dashboard_anasayfa`, `/products`, `/orders`, `/orders/create`.

7. Do cart / checkout / favorites / orders / account pages have auth control?
   - Orders have both auth routes and admin permission routes.
   - Runtime cart/checkout routes are not visible; only admin page builder routes exist.
   - Favorites / Wishlist routes are not visible.
   - Account routes are not visible.
   - Review routes are not visible.
   - Assumption: These domains are not yet complete at application route level.

8. Is page builder open only to admin?
   - Yes. `admin/pages*` routes are under `role:admin`.

9. Is dashboard builder open only to admin?
   - Yes. `admin/dashboard-builder*` and `admin/dashboard/blocks*` builder API routes are under `role:admin`.
   - Dashboard viewing route (`admin/dashboard`) is open to admin+secretary through permission.

10. Is permission checking centralized or scattered?
   - Scattered. Main route-level checks happen through `RoleFilter`.
   - `CampaignAccessFilter` uses separate campaign/coupon permission logic and requires role `admin`.
   - Some `Admin\Orders` methods use additional internal `canManageOrders()` checks.
   - `StockMove@store` has an extra role check for `manuel_duzeltme`.

## Detected Exposures

- Exposure:
  - Affected route: `/login/auth`, `/register/save`, and other POST routes
  - Affected domain: Auth / Admin Panel / Order / Product / Page Builder
  - Cause: Global `csrf` filter appears disabled in `app/Config/Filters.php`.
  - Risk: State-changing routes do not show route-level CSRF protection.
  - Suggested next analysis: `03_security_filter_audit.md`

- Exposure:
  - Affected route: `/logout`
  - Affected domain: Auth
  - Cause: Defined as public GET route; no `auth` filter.
  - Risk: It may be harmless for anonymous users, but session-destroying behavior can be triggered through public GET.
  - Suggested next analysis: `03_security_filter_audit.md`

- Exposure:
  - Affected route: `/admin/marketing`, `/admin/pricing*`, `/admin/automation`
  - Affected domain: Admin Panel / Product / Catalog / Campaign
  - Cause: Routes are protected by `role:admin`; no granular permission is used.
  - Risk: Operation-level separation inside admin role is not possible; consistency with the KB permission model is weak.
  - Suggested next analysis: `04_admin_permission_granularity.md`

- Exposure:
  - Affected route: `/admin/campaigns*`, `/admin/coupons*`
  - Affected domain: Campaign / Coupon / Secretary Access
  - Cause: `campaign_access` filter accepts only role `admin`.
  - Risk: If secretary campaign/coupon access is expected, route-level access blocks it; if not expected, this is OK but inconsistent with other secretary permission patterns.
  - Suggested next analysis: `04_admin_permission_granularity.md`

- Exposure:
  - Affected route: Cart / checkout / favorites / account / review public-user routes
  - Affected domain: Cart / Favorites / Wishlist / Review / Frontend Storefront
  - Cause: Related runtime routes are not visible in `Routes.php`.
  - Risk: There may be a gap between UI/task expectations and route reality.
  - Suggested next analysis: `05_frontend_user_flow_gap_analysis.md`

- Exposure:
  - Affected route: `/orders`
  - Affected domain: Order / User
  - Cause: Auth user route redirects through `OrderController@index` to `admin/orders`.
  - Risk: If a normal user order list is expected, the route points to admin panel behavior; the admin route should then block unauthorized users.
  - Suggested next analysis: `05_frontend_user_flow_gap_analysis.md`

## Assumptions

- Assumption: Route Name is `-` because named route aliases are not visible in `Routes.php`.
- Assumption: Routes returning JSON, redirect, file, or PDF responses are not considered missing a view.
- Assumption: `Admin\Orders@downloadInvoice`, `ship`, `cancel`, `return`, `startReturn`, `completeReturn`, and `addNote` exist in the later part of the controller file; route inventory and controller structure support this flow.
- Assumption: If `campaign_access` behavior is intentional, campaign/coupon routes are intentionally closed to secretary users; otherwise the domain policy needs_review.
- Assumption: Cart/checkout are currently represented as admin page builder config/preview, not runtime cart/payment flow.
- Assumption: Public product routes intentionally do not require login.

## Risks

- Wildcard route rows are easier to read but weaker for automated drift detection.
- Route-level filters do not capture every controller-internal authorization check.
- CSRF and secure header behavior cannot be fully judged from route mappings alone.
- Missing runtime user routes can hide product expectations that are not represented in `Routes.php`.

## Result

- Clearly protected routes:
  - `admin/dashboard`, `admin/orders*`, `admin/products*`, `admin/stock*`, `admin/shipping*`, `admin/notifications*`, `admin/customers`.
  - `admin/pages*`, `admin/dashboard-builder*`, `admin/dashboard/blocks*` are admin-only.
  - `dashboard_anasayfa`, auth-group `/products`, `/orders`, `/orders/create` are login-protected.

- Routes with missing filters:
  - Public storefront routes do not lack filters from a sensitive data perspective; they appear intended to be public.
  - `/logout` is public GET and should be reviewed from an Auth perspective.
  - Login/register POST must be public, but security audit is needed because CSRF is not globally enabled.

- Routes with unclear permissions:
  - `/admin/marketing`, `/admin/pricing*`, `/admin/automation`.
  - `/admin/campaigns*`, `/admin/coupons*` from secretary policy perspective.
  - `/admin/settings` general admin settings; no granular permission.

- Routes with missing controller/view links:
  - No active route appears to have a missing controller file.
  - `products/selection` uses `site/products/index`; `site/products/product_selection.php` is not route-bound.
  - `admin/stock/move/(:segment)` does not render `admin/stock/move.php`; it redirects.
  - `admin/page-versions/(:segment)` uses the same `PageController@show` method, so its exact intent needs review.

- Top 5 critical risks:
  - Global CSRF filter appears disabled.
  - `/logout` is public GET without auth filter.
  - Permission enforcement is scattered across `RoleFilter`, `CampaignAccessFilter`, and controller-internal checks.
  - Runtime cart/checkout/favorites/account/review user routes are not visible.
  - Some admin-only areas lack granular permissions: marketing, pricing, automation, settings.

- Next suggested KB file:
  - `ai/domain-kb/03_security_filter_audit.md`

## Last Normalization Notes

- Normalized to the KB claim schema on 2026-04-24.
- Added Scope, Source of Truth, Key Claims, Related Files, Risks, and normalization notes.
- This file remains a policy matrix; `06_route_baseline.md` now provides the concrete route baseline for future drift checks.
